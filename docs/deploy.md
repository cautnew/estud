# Plano de deploy

Este documento descreve como colocar a aplicação em produção usando Docker, Nginx, Supervisor e Redis. Os arquivos referenciados aqui vivem em `Dockerfile`, `docker-compose.yml` e `docker/`.

## Visão geral da infraestrutura

```
                        ┌──────────────┐
  Internet ── HTTPS ──▶ │   Nginx      │  (porta 80, TLS terminado antes/aqui)
                        └──────┬───────┘
                               │ FastCGI (porta 9000)
                        ┌──────▼───────┐
                        │  app (PHP-FPM)│──────────┐
                        └──────┬───────┘           │
                               │                    │
                        ┌──────▼───────┐    ┌───────▼────────┐
                        │    MySQL      │    │     Redis      │
                        └───────────────┘    └───────┬────────┘
                                                      │
                                              ┌───────▼────────┐
                                              │ worker (Supervisor) │
                                              │  - queue:work ×2    │
                                              │  - schedule:work    │
                                              │  - pulse:check      │
                                              └──────────────────────┘
```

`app` e `worker` são construídos a partir da **mesma imagem** (`Dockerfile`, targets `app` e `worker`) — o código é idêntico, só o processo em primeiro plano muda. Isso evita divergência entre o que roda web e o que roda em background.

## Build da imagem

O `Dockerfile` tem 4 estágios:

1. **frontend** (`node:22-alpine`): `npm ci && npm run build`, gera `public/build`.
2. **vendor** (`composer:2`): `composer install --no-dev --optimize-autoloader`, sem rodar scripts do artisan (o código da aplicação ainda não foi copiado nesse estágio).
3. **app** (`php:8.4-fpm-alpine`): copia o código, o `vendor/` do estágio 2 e o `public/build` do estágio 1; instala as extensões PHP necessárias (`pdo_mysql`, `intl`, `zip`, `gd`, `bcmath`, `mbstring`, `dom`, `exif`, `pcntl`, `opcache`); roda como `www-data`.
4. **worker** (`FROM app`): adiciona o pacote `supervisor` e usa `docker/supervisor/supervisord.conf` como comando principal.

```bash
docker compose build
```

## Subindo os serviços

1. Copie `.env.production.example` para `.env` e preencha os segredos (senha do banco, `APP_KEY`, credenciais de e-mail etc.). Gere a `APP_KEY` com `php artisan key:generate --show` antes do primeiro deploy (fora do container, ou via `docker compose run --rm app php artisan key:generate --show`).
2. Suba a infraestrutura:
   ```bash
   docker compose up -d
   ```
3. Rode as migrations (uma única vez por release, não em todo restart do container):
   ```bash
   docker compose exec app php artisan migrate --force
   ```
4. Aplique o cache de otimização (rotas, config, views, eventos):
   ```bash
   docker compose exec app php artisan optimize
   ```
   Esse passo é o motivo de `opcache.validate_timestamps=0` em `docker/php/opcache.ini` ser seguro: os caches de config/rotas não vão ficar desatualizados porque cada deploy reconstrói a imagem do zero.

## Nginx

`docker/nginx/default.conf` termina a conexão do cliente, serve os assets versionados de `public/build` diretamente (cache `immutable` de 1 ano, já que o Vite usa hash de conteúdo no nome do arquivo) e repassa tudo o mais para o `app` via FastCGI na porta 9000. TLS não é terminado aqui — assume-se um load balancer/proxy reverso na frente (ou adicione um bloco `listen 443 ssl` com certificados reais se este container for exposto diretamente à internet).

O volume nomeado `public-build` é compartilhado entre `app` e `nginx`: o Docker popula automaticamente um volume novo com o conteúdo que já existia naquele caminho na imagem `app` na primeira subida, então o nginx enxerga os mesmos assets sem precisar rodar `npm run build` por conta própria.

## Supervisor

`docker/supervisor/supervisord.conf` mantém três processos vivos no container `worker`:

- **`laravel-worker`** (2 processos): `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`. `--max-time` recicla cada worker a cada hora — o Supervisor reinicia automaticamente, o que evita acúmulo de memória de dependências de terceiros ao longo do tempo.
- **`laravel-scheduler`**: `php artisan schedule:work`, substituindo a entrada de crontab tradicional (`* * * * * php artisan schedule:run`) — roda em loop dentro do próprio container.
- **`pulse-check`**: `php artisan pulse:check`, necessário para o card "Servers" do Pulse (uso de CPU/memória/disco).

Ajuste `numprocs` em `laravel-worker` conforme o volume real de jobs (hoje: notificação de revisões vencidas, recálculo de ciclo de estudos).

## Redis

Um único container `redis:7-alpine` com `--appendonly yes` (persistência) e senha via `REDIS_PASSWORD` atende três papéis, cada um com seu próprio "database" lógico do Redis (configurável em `config/database.php`, chave `redis`):

- **Cache** (`CACHE_STORE=redis`) — dashboard, health score e recomendações (ver `docs/production-checklist.md`).
- **Filas** (`QUEUE_CONNECTION=redis`) — consumidas pelos workers do Supervisor.
- **Sessões** (`SESSION_DRIVER=redis`).

Para volumes de tráfego maiores, separar cache/filas/sessões em instâncias Redis distintas evita que uma fila cheia pressione a latência de leitura de sessão — não é necessário no lançamento inicial.

## MySQL

`mysql:8.4`, volume nomeado `mysql-data` para persistência, healthcheck via `mysqladmin ping` (o `app` só sobe depois que o MySQL responde). Backups **não** estão automatizados neste compose — ver checklist de produção.

## Zero-downtime deploy (esboço)

Este `docker-compose.yml` é adequado para um único host. Para deploys sem downtime:

1. `docker compose build` a nova imagem.
2. `docker compose up -d --no-deps app worker` — o Compose recria `app`/`worker` com a imagem nova; `nginx` continua servindo a imagem antiga até o novo `app` responder ao healthcheck (adicione um `healthcheck` no serviço `app` chamando `/up` antes de depender disso).
3. Rode `migrate --force` **antes** do passo 2 se a migration for aditiva/compatível com o código antigo; migrations destrutivas exigem uma janela de manutenção.

Para múltiplos hosts, considere Laravel Cloud, Kubernetes ou um orquestrador equivalente — este plano cobre o caso de um único servidor Docker Compose.
