# API PDV - Guia de Configuração

## Sobre a API

A API PDV é uma aplicação desenvolvida para fornecer um serviço de disponibilização de informações para que nossos integradores possam estar sincronizados com a base de dados da Enjoy. Esta API foi feita no padrão REST, o que possibilita a manipulação de clientes, produtos e até os pedidos da loja virtual.

## Tecnologias Utilizadas
- Laravel
- PHP 8.2
- Composer
- MySQL

## Montagem da Aplicação

1. **Clone o Repositório:**

```bash
git clone https://github.com/HoogliHub/api_pdv.git
```

2. **Copie o Arquivo de Configuração:**

    - Para Linux:

```bash
cp .env.example .env
```

- Para Windows:

```bash
copy .env.example .env
```

3. **Instale as Dependências com o Composer:**

```bash
composer install
```

4. **Execute as Migrações para Criar o Banco de Dados:**

```bash
php artisan migrate
```

5. **Inicie o Servidor:**

```bash
php artisan serve
```

Agora a aplicação está pronta para ser utilizada! Certifique-se de configurar corretamente o arquivo `.env` com as informações do banco de dados e outras configurações específicas do seu ambiente.

Em caso de dúvidas ou problemas, entre em contato com a equipe de suporte.
