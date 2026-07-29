# Domain

Camada que representa as regras de negócio e os conceitos centrais da aplicação, sem depender do framework ou de qualquer detalhe de infraestrutura.

Aqui viverão, no futuro, entidades, objetos de valor, contratos de repositório (interfaces), eventos de domínio e exceções específicas do negócio. O código desta camada não deve importar nada do Eloquent, do Illuminate ou de qualquer pacote de infraestrutura — apenas PHP puro e outros elementos do próprio Domain.

Consulte `docs/architecture.md` para o mapa completo das camadas e as regras de dependência entre elas.

Esta pasta está intencionalmente vazia nesta mudança: nenhuma regra de negócio foi migrada ou escrita ainda.
