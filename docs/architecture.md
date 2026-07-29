# Arquitetura do projeto

Este documento descreve a organização arquitetural da aplicação: as camadas lógicas do backend, a estrutura do frontend, as convenções de componentes e o tooling configurado. Ele reflete o estado **fundacional** do projeto — a infraestrutura e as convenções estão prontas, mas nenhuma regra de negócio foi implementada ainda.

## Visão geral

A aplicação é um monólito Laravel 13 + Inertia v3, servindo uma SPA React 19 com TypeScript, Tailwind v4 e MySQL (via MariaDB, wire-compatible). O backend é organizado em camadas inspiradas em Clean Architecture/DDD; o frontend segue a estrutura padrão do `laravel/react-starter-kit`.

## Mapa de camadas (backend)

| Camada | Pasta | Responsabilidade |
|---|---|---|
| **Presentation** | `app/Http/**` + `resources/js/**` | Entrada e saída da aplicação: controllers, form requests, middlewares, rotas (`routes/*.php`) e toda a interface React/Inertia. Traduz requisições HTTP em chamadas para a Application e renderiza as respostas. |
| **Application** | `app/Application/**` | Orquestra casos de uso, combinando Domain e Infrastructure. Não contém regra de negócio própria nem detalhes concretos de persistência/integração. |
| **Domain** | `app/Domain/**` | Entidades, objetos de valor, contratos de repositório (interfaces), eventos e exceções de negócio. Não depende do framework nem de infraestrutura. |
| **Infrastructure** | `app/Infrastructure/**` | Implementações concretas dos contratos do Domain: repositórios Eloquent, integrações externas, adapters de fila/cache/storage. |

**Regra de dependência**: `Presentation → Application → Domain`. `Infrastructure` implementa contratos definidos em `Domain` e é injetada via container do Laravel — nunca o inverso. `Domain` não deve importar nada do Illuminate/Eloquent.

**Decisão de design**: `app/Http` permanece exatamente onde está — não foi fisicamente movido para `app/Presentation` — porque Fortify, Wayfinder e as rotas já existentes dependem do namespace atual (`App\Http\*`), e uma migração física traria risco desproporcional para uma mudança que é puramente fundacional. `app/Http` (backend) + `resources/js` (frontend) formam, juntos, a camada Presentation conceitual.

**Estado atual**: `app/Domain`, `app/Application` e `app/Infrastructure` foram criados vazios (cada um com um `README.md` explicando seu propósito). Toda a lógica existente (autenticação Fortify, `ProfileController`, `SecurityController`, etc.) permanece em `app/Http` e `app/Actions/Fortify`, intocada. Nenhuma regra de negócio foi migrada ou escrita nesta mudança.

Um teste de arquitetura (`arch()` do Pest, com `->toOnlyUse()`/`->not->toUse()` entre camadas) fica deliberadamente adiado até existir código real em `Domain`/`Application` — não há nada significativo para asserir contra namespaces vazios.

## Estrutura do frontend (`resources/js/`)

Nenhuma pasta nova foi criada aqui — a estrutura padrão do starter kit já representa adequadamente a camada Presentation:

- `pages/` — páginas Inertia, uma por rota. Convenção: arquivo kebab-case, export default em PascalCase igual ao nome do arquivo, pastas aninhadas espelham o namespace da rota (`auth/`, `settings/`). Páginas usam o padrão `Componente.layout = { breadcrumbs: [...] }` para layouts persistentes.
- `layouts/` — layouts reutilizáveis (`app-layout.tsx`, `auth-layout.tsx`, `settings/layout.tsx` e variantes).
- `components/` — componentes de aplicação (não vendorizados). São lintados e formatados normalmente: export default, aspas simples, ponto e vírgula, indentação de 4 espaços.
- `components/ui/` — primitivos shadcn ("new-york", `baseColor: neutral`, ver `components.json`). Convenção própria (herdada do shadcn): export nomeado, `data-slot`, `cn()`, aspas duplas, sem ponto e vírgula, indentação de 2 espaços. **Esta pasta é excluída do ESLint e do Prettier** (ver `eslint.config.js` e `.prettierignore`) — é tratada como código vendorizado, assim como `resources/js/actions/**` e `resources/js/routes/**` (gerados pelo Wayfinder).
- `hooks/`, `lib/`, `types/` — utilitários, hooks e tipos compartilhados.

Novas "pastas de domínio" no frontend (ex: agrupar páginas por feature) não foram introduzidas agora — devem surgir organicamente conforme features reais forem implementadas, para evitar estrutura especulativa.

## Sistemas já existentes (reaproveitados, não recriados)

- **Layout principal**: `layouts/app-layout.tsx` → `layouts/app/app-sidebar-layout.tsx`, compõe `AppShell` + `AppSidebar` + `AppContent` + `AppSidebarHeader`. Também existe uma variante de header (`layouts/app/app-header-layout.tsx`).
- **Menu lateral responsivo**: `components/app-sidebar.tsx`, construído sobre o primitivo `Sidebar` do shadcn (`collapsible="icon"`), com fallback para `Sheet` em telas pequenas (`hooks/use-mobile.tsx`).
- **Tema claro/escuro**: `hooks/use-appearance.tsx` (modos `light`/`dark`/`system`, persistência em `localStorage` + cookie, `initializeTheme()` chamado em `app.tsx`) combinado com o middleware `App\Http\Middleware\HandleAppearance` (lê o cookie no servidor e evita flash do tema errado no primeiro paint) e a UI `components/appearance-tabs.tsx` (usada em `pages/settings/appearance.tsx`). Os tokens de cor (`resources/css/app.css`) já incluem `--chart-1` a `--chart-5` em oklch, prontos para uso com Recharts.

Nenhum desses sistemas precisou ser criado — apenas documentados aqui.

## Componentes reutilizáveis

| Componente | Local | Status |
|---|---|---|
| `Card`, `CardHeader`, `CardTitle`, etc. | `components/ui/card.tsx` | Já existia — reaproveitado sem alteração. |
| `Badge` | `components/ui/badge.tsx` | Já existia — reaproveitado sem alteração. |
| `Dialog` (= Modal) | `components/ui/dialog.tsx` | Já existia. **Não foi criado um componente `Modal` separado** — `Dialog` já cumpre esse papel (`Dialog`, `DialogTrigger`, `DialogContent`, `DialogHeader`, `DialogFooter`, `DialogTitle`, `DialogDescription`). Use `Dialog` sempre que a tarefa pedir um "modal". |
| `Progress` (= ProgressBar) | `components/ui/progress.tsx` (novo) | Criado nesta mudança sobre `@radix-ui/react-progress`. O export mantém o nome canônico do shadcn (`Progress`, não `ProgressBar`) para preservar compatibilidade com futuras atualizações via CLI do shadcn. Uso: `<Progress value={42} />`. |
| `EmptyState` | `components/ui/empty-state.tsx` (novo) | Componente composto (padrão inspirado em `Card`): `EmptyState`, `EmptyStateIcon`, `EmptyStateTitle`, `EmptyStateDescription`, `EmptyStateAction`. |
| `Table` (primitivos) | `components/ui/table.tsx` (novo) | Primitivos HTML de tabela estilizados (`Table`, `TableHeader`, `TableBody`, `TableFooter`, `TableRow`, `TableHead`, `TableCell`, `TableCaption`). Radix não tem primitivo de tabela — é HTML puro com classes. |
| `DataTable` | `components/data-table.tsx` (novo) | Wrapper genérico sobre `@tanstack/react-table` + os primitivos `Table*`. Props: `columns`, `data`, `isLoading?` (renderiza linhas de `Skeleton`), `emptyTitle?`/`emptyDescription?` (renderiza `EmptyState` quando não há linhas). **Escopo intencionalmente sem** ordenação, paginação ou filtros (`getSortedRowModel`/`getPaginationRowModel`) — ponto de extensão futuro, a ser adicionado quando houver um caso de uso real. Fica fora de `components/ui/` (é lintado/formatado normalmente) porque é um wrapper de aplicação que depende de `ColumnDef`s fornecidos pelo chamador, não um primitivo vendorizado. |

## Data fetching e gráficos

- **React Query** (`@tanstack/react-query`) foi instalado e conectado globalmente: `resources/js/lib/query-client.ts` exporta `createQueryClient()`, e `resources/js/app.tsx` instancia um `QueryClient` único em escopo de módulo, envolvendo toda a aplicação com `<QueryClientProvider>` (dentro do `withApp` do Inertia, por fora do `TooltipProvider`/`Toaster` existentes). Não há `resources/js/ssr.tsx` no projeto, então o singleton em escopo de módulo é seguro.
- **Recharts** foi apenas instalado como dependência — nenhum gráfico ou wrapper foi criado, pois nenhuma regra de negócio/dado real existe ainda para visualizar. Quando o primeiro gráfico for necessário, considere criar um pequeno helper (`resources/js/lib/chart-theme.ts`) que leia os tokens `--chart-1..5` de `resources/css/app.css` e os exponha como array de cores para o Recharts — isso foi deliberadamente **adiado** (YAGNI) nesta mudança, para não introduzir abstração sem consumidor.

## Tooling

- **ESLint** (`eslint.config.js`), **Prettier** (`.prettierrc`/`.prettierignore`) — já configurados; cobrem todo o `resources/js` exceto os primitivos vendorizados (`components/ui/*`) e código gerado (`actions/**`, `routes/**`, `wayfinder/**`). Nenhuma alteração foi necessária para os novos arquivos.
- **Laravel Pint** (`pint.json`, preset `laravel`) — cobre `app/` recursivamente, incluindo as novas pastas `Domain`/`Application`/`Infrastructure` automaticamente (nenhuma configuração adicional necessária).
- **Larastan/PHPStan** (`phpstan.neon`, nível 7) — mesma cobertura recursiva de `app/`.
- **"PHP CS Fixer"**: o projeto **não** adiciona um pacote `friendsofphp/php-cs-fixer` separado, porque o **Laravel Pint já o envolve internamente**. Adicionar um pacote redundante duplicaria a responsabilidade sem benefício — Pint é o ponto único de formatação PHP do projeto (`composer run lint` / `composer run lint:check`).

## MySQL

O ambiente de desenvolvimento (DDEV, `.ddev/config.yaml`) já roda **MariaDB 11.8**, wire-compatible com MySQL, e o `.env` já aponta `DB_CONNECTION=mariadb` para o serviço `db` do DDEV. `config/database.php` resolve o driver `mariadb` através do bloco de conexão `mysql`. Nada precisou ser configurado nesta mudança.
