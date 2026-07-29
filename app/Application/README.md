# Application

Camada que orquestra os casos de uso da aplicação, coordenando o Domain e a Infrastructure para atender às necessidades da Presentation.

Aqui viverão, no futuro, casos de uso/ações de aplicação, DTOs de entrada e saída, e serviços de aplicação que combinam múltiplas operações de domínio. Esta camada pode depender do Domain, mas não deve conter regra de negócio própria nem detalhes concretos de infraestrutura (banco de dados, filas, APIs externas) — esses detalhes ficam por trás dos contratos definidos no Domain e implementados na Infrastructure.

Consulte `docs/architecture.md` para o mapa completo das camadas e as regras de dependência entre elas.

Esta pasta está intencionalmente vazia nesta mudança: nenhuma regra de negócio foi migrada ou escrita ainda.
