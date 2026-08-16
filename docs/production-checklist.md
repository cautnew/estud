# Checklist de produção

Checklist do que foi implementado nesta rodada de preparação para produção, e do que ainda depende de uma decisão operacional (segredos, domínio, provedor) antes do primeiro deploy. Ver também `docs/deploy.md` para o plano de infraestrutura.

## Auditoria

- [x] `spatie/laravel-activitylog` instalado e migrado (tabela `activity_log`).
- [x] Trait `App\Models\Concerns\Auditable` (log de criação/atualização/exclusão, só campos alterados, sem logs vazios) aplicada aos modelos de domínio: `Subject`, `Topic`, `StudyPlan`, `Schedule`, `StudySession`, `Review`, `QuestionLog`, `Goal`, `ExamNotice`, `StudyCycle`, `User`.
- [x] Painel `admin/activity-log` (filtros por evento, tipo, usuário e período; visualização do diff antigo/novo por registro).
- [ ] Definir `clean_after_days` em `config/activitylog.php` (365 por padrão) de acordo com a política de retenção real, e agendar `activitylog:clean` em `routes/console.php` se a retenção precisar ser ativamente aplicada (hoje o comando existe mas não está agendado).

## Logs

- [x] `config/logging.php` já é 100% orientado a env — nenhuma mudança de código necessária.
- [ ] Em produção, usar `LOG_STACK=daily`, `LOG_LEVEL=warning` (ou `error`), `LOG_DAILY_DAYS=14` (ver `.env.production.example`).
- [ ] Opcional: configurar `LOG_SLACK_WEBHOOK_URL` para receber `critical`+ em um canal do Slack (`config/logging.php` já suporta, canal `slack`).

## Cache

- [x] `predis/predis` instalado; `config/database.php` (conexão `redis`) e `config/cache.php` (store `redis`) já vêm prontos no framework, só precisam de `CACHE_STORE=redis` no `.env`.
- [x] `DashboardController` cacheia `CalculateDashboardStats` por 120s por usuário (a sessão ativa do cronômetro nunca é cacheada).
- [x] `RecommendationController` cacheia Health Score e recomendações de estudo por 300s por usuário; recomendações de revisão **não** são cacheadas, pois refletem ações imediatas do usuário (concluir/pular revisão).
- [ ] Sem invalidação ativa por evento — a equipe aceitou staleness de até 120s/300s em troca de não precisar instrumentar todo write path. Se isso passar a incomodar, considerar `Cache::forget()` nos pontos de escrita relevantes (`StudySessionController`, `ReviewController`, `GoalController`).

## Filas e jobs

- [x] `QUEUE_CONNECTION=redis` recomendado em produção (`.env.production.example`); `database` continua sendo o padrão local.
- [x] `NotifyDueReviewJob` e `RecalculateStudyCycleOrderJob` com `$tries=3`, `backoff()` (10s/30s/60s) e `$timeout=30`.
- [x] Workers gerenciados pelo Supervisor (`docker/supervisor/supervisord.conf`), 2 processos, `--max-time=3600` para reciclagem periódica.
- [ ] Configurar alerta para `failed_jobs` não vazio (Pulse mostra isso no card de filas; considerar também `php artisan queue:monitor` com um limiar).

## Monitoramento

- [x] `laravel/pulse` instalado e migrado; dashboard em `/pulse`.
- [x] Gate `viewPulse` restrito à role `Admin` (`AppServiceProvider::configureAuthorization()`), válido em todo ambiente — não depende do padrão "local only" do Pulse.
- [x] `pulse:check` gerenciado pelo Supervisor (necessário para o card de servidores).
- [ ] Se o volume de requisições justificar, mudar `PULSE_INGEST_DRIVER=redis` e habilitar o programa `pulse-work` no Supervisor (fora da caixa de entrada do request cycle).
- [ ] Nenhum serviço externo de error tracking (Sentry, Bugsnag) foi adicionado — avaliar se o Pulse + logs são suficientes ou se vale a pena depender de mais um serviço externo.

## Rate limiting

- [x] Limiter nomeado `exports` (10 req/min por usuário, fallback por IP) aplicado às rotas `reports/export/*` (`routes/reports.php`), que disparam geração de PDF/Excel.
- [x] Login já tem limiter próprio via Fortify (`config/fortify.php`, `limiters.login`).
- [ ] Avaliar se outros endpoints de escrita intensiva (ex: registro de sessão de estudo) precisam de throttle — não aplicado agora por não haver evidência de abuso.

## Hardening de segurança

- [x] Middleware `App\Http\Middleware\SecurityHeaders`: `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy` em toda resposta; `Content-Security-Policy` e `Strict-Transport-Security` só fora de ambiente local (o dev server do Vite precisa de HMR/eval, incompatível com CSP estrita).
- [x] `TrustProxies` configurado em `bootstrap/app.php` via `TRUSTED_PROXIES` — necessário para `request()->secure()`/IP corretos atrás do Nginx.
- [x] `URL::forceScheme('https')` em produção (`AppServiceProvider`), para geração de URLs/assets corretas mesmo com PHP-FPM recebendo tráfego HTTP puro internamente.
- [x] `Password::defaults()` já exigia senha forte em produção (pré-existente).
- [x] `DB::prohibitDestructiveCommands()` já ativo em produção (pré-existente).
- [x] `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax` recomendados em `.env.production.example`.
- [ ] TLS real: gerar/renovar certificado (Let's Encrypt via um proxy como Traefik/Caddy na frente, ou no load balancer) — não coberto pelo `docker-compose.yml`, que assume TLS terminado antes do Nginx.
- [ ] Rodar `composer audit` e `npm audit` no pipeline de CI antes de cada deploy.

## Otimizações MySQL

- [x] Índices compostos já existiam nos pontos quentes reais (verificado, não adicionado por adicionar): `study_sessions(owner_id, date)`, `question_logs(owner_id, date)` e `(owner_id, subject_id)`, `reviews(owner_id, status, scheduled_for)`, `goal_progress(goal_id, period_start)` (único).
- [x] `config/database.php` já usa `'strict' => true` na conexão MySQL.
- [ ] Tuning de servidor (fora do alcance do código da aplicação): `innodb_buffer_pool_size` em torno de 60–70% da RAM disponível ao container do MySQL, `slow_query_log` habilitado com `long_query_time` baixo durante a fase de warm-up para achar queries lentas reais (cruzar com o card "Slow Queries" do Pulse em vez de adivinhar).
- [ ] Backup automatizado (`mysqldump`/`mariabackup` agendado + retenção) — não incluído no `docker-compose.yml`.

## Otimizações React

- [x] `vite.config.ts`: `manualChunks` separando `react`, `recharts`, `react-big-calendar`, `react-dnd` e `@tanstack/*` em chunks próprios, para não pagar o custo dessas libs em páginas que não as usam e para não invalidar o cache do bundle inteiro a cada mudança de código de app.
- [x] `esbuild.drop` remove `console`/`debugger` do bundle de produção (mantido em dev).
- [x] Cada página Inertia já é um chunk separado automaticamente via `@inertiajs/vite` — nenhuma mudança necessária aí.
- [ ] Sem auditoria manual de re-renders/memoização componente a componente — se um perfil real (React DevTools Profiler) apontar um componente específico como gargalo, tratar pontualmente em vez de aplicar `memo`/`useMemo` especulativamente em todo lugar.

## Deploy

- [x] `Dockerfile` multi-stage (frontend, vendor, app, worker), `docker-compose.yml` (app, worker, nginx, mysql, redis), `docker/nginx/default.conf`, `docker/supervisor/supervisord.conf`, `docker/php/{opcache,production}.ini`. Detalhes em `docs/deploy.md`.
- [ ] `APP_KEY`, credenciais de banco, `REDIS_PASSWORD` e credenciais de e-mail reais — preencher em `.env` no servidor a partir de `.env.production.example`, nunca commitar.
- [ ] Domínio + DNS + certificado TLS — depende do provedor escolhido.
- [ ] CI que rode `php artisan test`, `phpstan`, `pint --test`, `npm run lint:check`, `npm run types:check` antes de cada deploy (o script `composer ci:check` já reúne quase tudo isso localmente).
