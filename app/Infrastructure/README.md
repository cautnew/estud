# Infrastructure

Camada que implementa os detalhes técnicos concretos exigidos pelos contratos definidos no Domain e consumidos pela Application.

Aqui viverão, no futuro, implementações Eloquent de repositórios, integrações com serviços externos, adapters de filas/cache/storage e qualquer outro código que amarre a aplicação a uma tecnologia específica. Esta é a única camada que deve depender diretamente do Illuminate/Eloquent para fins de persistência e integrações externas.

Consulte `docs/architecture.md` para o mapa completo das camadas e as regras de dependência entre elas.

Esta pasta está intencionalmente vazia nesta mudança: nenhuma regra de negócio foi migrada ou escrita ainda.
