# Motor de recomendações e Health Score

Este documento descreve o algoritmo do motor de recomendações: os três painéis diários (o que estudar hoje, o que revisar hoje, o que está atrasado) e o **Health Score** (0 a 100), que resume a saúde geral dos estudos do usuário. A implementação vive em `app/Actions/Recommendations/`.

## Painéis

| Painel | Classe | Fonte de dados |
|---|---|---|
| O que estudar hoje | `GenerateStudyRecommendations` | Cronograma (`ScheduleItem`), ciclo de estudos (`StudyCycleItem`), plano de estudos (`StudyPlanTopic`) |
| O que revisar hoje | `GenerateReviewRecommendations` (`dueToday`) | Revisões (`Review`) com `scheduled_for` = hoje |
| O que está atrasado | `GenerateReviewRecommendations` (`overdue`) | Revisões (`Review`) com `scheduled_for` < hoje, ainda pendentes |

### O que estudar hoje

Combina três sinais independentes, **concatenados em ordem de prioridade** — não são mesclados em uma única lista ranqueada por um score comum, porque não estão na mesma escala numérica: a prioridade do cronograma é um ordinal arbitrário (`ScheduleItem.priority`, menor = mais importante), a prioridade do ciclo de estudos é 0-100 (`StudyCycleItem.priority_score`, calculado por `CalculateStudyCycleItemScore`), e os tópicos do plano de estudos não têm pontuação alguma. Forçar os três em um único eixo fabricaria uma precisão que os dados não sustentam.

1. **Cronograma** — todos os `ScheduleItem` cujo dia bate com hoje (ver "Ocorrência do cronograma" abaixo), ordenados por `priority`. É o sinal mais concreto: o usuário planejou explicitamente estudar isso hoje.
2. **Ciclo de estudos** — até 3 `StudyCycleItem` com `progress < 100`, ordenados por `priority_score` decrescente. `priority_score` já é mantido atualizado por `RecalculateStudyCycleOrderJob`/`CalculateStudyCycleItemScore`; esta action não o recalcula.
3. **Plano de estudos** — até 5 `StudyPlanTopic` com status `Pending` ou `InProgress` (tópicos `InProgress` vêm primeiro, depois `Pending`, cada grupo mantendo a ordem `order` do plano). Funciona como um backlog de fallback, usado quando os sinais acima estão vazios ou esgotados.

#### Ocorrência do cronograma

`ScheduleItem` guarda **apenas** `day_of_week` OU `day_of_month`, sem nenhuma data-âncora em `Schedule` ou `ScheduleItem`. A regra de ocorrência (`DeterminesScheduleOccurrence::scheduleItemOccursOn`) é:

- `day_of_week` preenchido → ocorre quando `day_of_week === hoje->dayOfWeek`.
- `day_of_month` preenchido → ocorre quando `day_of_month === hoje->day`.
- Nenhum dos dois preenchido (cronogramas `Daily`) → ocorre todo dia.

**Limitação conhecida e deliberada**: como não há data-âncora, cadências alternadas (`Biweekly`, `Quarterly`) não podem ser distinguidas de suas contrapartes simples (`Weekly`, `Monthly`) — ambas caem na mesma regra de "mesmo dia da semana/mês, toda vez". Ou seja, um item quinzenal hoje é tratado como se fosse semanal. Corrigir isso exigiria adicionar uma data de início ao modelo `Schedule`, o que foi deixado fora de escopo por não ter sido pedido nem ter um consumidor real ainda.

### O que revisar hoje / O que está atrasado

Ambos vêm da mesma query base: revisões (`Review`) do usuário com `status = Pending` e `scheduled_for <= hoje`, particionadas por `scheduled_for < hoje` (atrasado) vs. `scheduled_for = hoje` (hoje). Cada item retorna `days_overdue` (0 para o painel de hoje).

## Health Score (0-100)

Calculado por `CalculateHealthScore`, soma de 4 componentes com pesos fixos que somam 100 pontos:

| Componente | Peso | O que mede |
|---|---|---|
| Constância | 30 | Hábito de estudo sustentado + sequência atual (streak) |
| Metas | 25 | Proporção de metas em dia no período atual |
| Revisões | 25 | Cumprimento das revisões que já venceram, penalizado por atraso |
| Cronograma | 20 | Aderência das sessões de estudo ao cronograma planejado |

O score final é `round(soma dos 4 componentes)`, um inteiro de 0 a 100. Um rótulo qualitativo acompanha o número:

`score ≥ 80` → Excelente · `≥ 60` → Bom · `≥ 40` → Regular · `≥ 20` → Atenção · `< 20` → Crítico

### Constância (peso 30)

Metade do peso vem da **proporção de dias ativos** nos últimos `CONSISTENCY_WINDOW_DAYS = 14` dias (dia ativo = `StudySession` daquele dia com `duration_seconds` somado > 0). A outra metade vem da **sequência atual (streak)**: dias consecutivos ativos, contados de trás para frente a partir de hoje — ou de ontem, se hoje ainda não tem atividade registrada (dia de tolerância, para não zerar uma sequência em andamento antes do dia terminar). O streak é normalizado por `STREAK_TARGET_DAYS = 7` (streak de 7+ dias vale nota máxima nessa metade).

```
activityRatio = diasAtivos / 14
streakRatio   = min(streakAtual / 7, 1)
score         = (activityRatio * 0.5 + streakRatio * 0.5) * 30
```

### Metas (peso 25)

Reaproveita a mesma lógica de `CalculateDashboardStats::goalsIndicator()`: para cada `Goal`, verifica se existe um `GoalProgress` cujo `period_start` bate com o período atual (`Goal::currentPeriodBounds()`) e `achieved = true`.

```
score = (metasEmDia / totalMetas) * 25
```

**Decisão de design**: se o usuário não tem nenhuma meta cadastrada, o componente vale **0**, não uma nota neutra/máxima. Isso é proposital — o Health Score mede engajamento real com cada prática, não "ausência de reprovação". O efeito colateral é incentivar o cadastro de metas. Uma melhoria futura razoável seria redistribuir o peso de componentes "não aplicáveis" entre os demais, mas isso foi deixado de fora por ora para manter o algoritmo simples e literalmente auditável a partir deste documento (ver `details` retornado por componente, usado justamente para essa auditoria).

### Revisões (peso 25)

Olha para as revisões que **já venceram** dentro de uma janela de `REVIEW_WINDOW_DAYS = 60` dias (`scheduled_for` entre hoje-60 e hoje, independente do status) e calcula a proporção concluída:

```
base = (revisõesConcluídas / revisõesVencidas) * 25
```

Em seguida subtrai uma penalidade pelo **backlog atual** de revisões atrasadas (`status = Pending` e `scheduled_for < hoje`, sem limite de janela — atraso é atraso, não importa há quanto tempo):

```
penalidade = min(revisõesAtrasadas * 2, 10)
score      = max(base - penalidade, 0)
```

A penalidade existe para que um bom histórico de conclusão não esconda um backlog grande acumulado recentemente; o teto de 10 pontos evita que um usuário com muitas revisões pendentes fique com o componente inteiro negativo.

Se não há nenhuma revisão vencida na janela, o componente vale 0 (ainda não há hábito de revisão a medir).

### Cronograma (peso 20)

Para cada um dos últimos `SCHEDULE_WINDOW_DAYS = 7` dias, verifica quais `ScheduleItem` do usuário "ocorreriam" naquele dia (mesma regra de `scheduleItemOccursOn` usada no painel "O que estudar hoje") e se existe uma `StudySession` com `duration_seconds > 0` para a mesma disciplina naquele mesmo dia:

```
score = (paresCorrespondidos / paresEsperados) * 20
```

Se o usuário não tem nenhum `Schedule`/`ScheduleItem` cadastrado, o componente vale 0.

## Auditabilidade

Cada componente retorna `score`, `weight` e um `details` com os números brutos usados no cálculo (`activeDays`/`currentStreak`, `onTrack`/`total`, `completed`/`due`/`overdue`, `matched`/`occurrences`). O payload completo é exposto ao frontend via `RecommendationController::index` — qualquer divergência entre o número exibido e o que o usuário espera pode ser depurada olhando esses valores brutos, sem precisar reimplementar a fórmula.

## Não incluído nesta versão

- Redistribuição de peso quando um componente não é aplicável (ver nota em "Metas").
- Suporte a data-âncora para cronogramas quinzenais/trimestrais (ver "Ocorrência do cronograma").
- Um único score de prioridade unificando cronograma/ciclo/plano no painel "O que estudar hoje" (ver justificativa na seção do painel).

Essas lacunas são deliberadas — cada uma exigiria mudanças de schema ou uma escolha de produto que não foi pedida, e foram deixadas de fora para não introduzir complexidade sem um caso de uso real por trás (YAGNI).
