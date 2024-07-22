
# Sistema de Processamento de Boletos

Este projeto é um sistema de processamento de boletos que permite o upload de arquivos CSV, valida e processa os dados contidos nos arquivos e despacha jobs para geração de boletos e envio de emails.

## Requisitos

- Docker
- Docker Compose

## Configuração

### Passo 1: Clonar o Repositório

Clone este repositório para a sua máquina local usando o seguinte comando:

```sh
git clone https://github.com/seu-usuario/nome-do-repositorio.git
cd nome-do-repositorio
```

### Passo 2: Configurar as Variáveis de Ambiente

Renomeie o arquivo `.env.example` para `.env` as configurações estão feitas a carater de teste local.

```sh
cp .env.example .env
```

### Passo 3: Construir e Iniciar os Contêineres

Execute o seguinte comando para construir e iniciar os contêineres Docker:

```sh
docker-compose up --build -d
```

### Passo 4: Instalar Dependências

Acesse o contêiner da aplicação e instale as dependências do Laravel usando o Composer:

```sh
docker-compose exec app bash
composer install
```

### Passo 5: Gerar a Chave da Aplicação

Dentro do contêiner da aplicação, gere a chave da aplicação Laravel:

```sh
php artisan key:generate
```

### Passo 6: Executar as Migrações

Execute as migrações para criar as tabelas no banco de dados:

```sh
php artisan migrate
```

## Executando a Aplicação

A aplicação deve estar acessível em [http://localhost:8989](http://localhost:8989).

## Endpoints

### Upload de Arquivo CSV

- **URL:** `/api/upload`
- **Método:** `POST`
- **Parâmetros:**
  - `files[]` (obrigatório): Arquivo(s) CSV para upload.

### Exemplo de Requisição

Use uma ferramenta como o Postman ou `curl` para testar o upload de arquivos CSV.

```sh
curl -X POST http://localhost:8989/api/upload   -F 'files[]=@path/to/your/input.csv'
```

## Testes

### Executar Testes

Para executar os testes, use o seguinte comando:

```sh
docker-compose exec app bash
php artisan test
```

### Estrutura de Testes

- **Testes Unitários:** Verificam componentes individuais do sistema.
- **Testes de Integração:** Verificam o funcionamento conjunto de múltiplos componentes.

## Estrutura do Projeto

```plaintext
app
├── Console
├── Exceptions
├── Http
│   ├── Controllers
│   ├── Middleware
├── Jobs
├── Models
├── Providers
├── Services
bootstrap
config
database
├── factories
├── migrations
├── seeders
public
resources
routes
tests
├── Feature
├── Unit
```

## Contribuição

1. Faça um fork do projeto.
2. Crie uma branch para sua feature (`git checkout -b feature/fooBar`).
3. Faça commit das suas alterações (`git commit -am 'Add some fooBar'`).
4. Faça push para a branch (`git push origin feature/fooBar`).
5. Crie um novo Pull Request.

## Licença

Distribuído sob a licença MIT. Veja `LICENSE` para mais informações.
