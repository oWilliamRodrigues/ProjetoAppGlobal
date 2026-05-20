# App Treinamento (Gerente de estoque) 

Pequeno projeto com CRUD em PHP (Laravel), React, MySQL e Docker. Feito especificamente para aprendizado das tecnologias.

Adam Ribeiro e William Rodrigues

# Passo a passo de instalação

## 1. Pré-requisitos
Instale antes de mais nada:

Git — clonar o repo
Docker Desktop (Windows/Mac) ou Docker Engine + Compose (Linux)
WSL2 se for Windows (recomendado)
Verifique:


git --version
docker --version
docker compose version
Sem esses três passando, não adianta seguir.

## 2. Clonar e entrar na pasta

git clone https://github.com/<org>/<repo>.git inventory-app
cd inventory-app

## 3. Preparar o .env

cp .env.example .env
Edite o .env e adicione a linha do JWT (não está no example):

JWT_SECRET=
Pode deixar vazia agora — vamos gerar no Passo 6.

## 4. Subir os containers

docker compose up -d
Os 4 serviços sobem:

db (MySQL 8 — leva ~30s pra ficar healthy na primeira vez)
app (PHP 8.4-fpm)
nginx (porta 80 exposta)
node (Vite em 5173, instala node_modules na primeira execução)
Veja o status:


docker compose ps
Todos devem estar Up ou Up (healthy). Se node ficar reiniciando, veja docker compose logs node — geralmente é conflito de dependências (já passou por isso na sua jornada).

## 5. Instalar dependências PHP

docker compose exec app composer install
Isso popula vendor/. Demora ~1 min na primeira vez.

## 6. Gerar chaves de aplicação
Duas chaves diferentes, ambas obrigatórias:

docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
APP_KEY — usada pelo Laravel para criptografia simétrica (sessões, cookies, etc).
JWT_SECRET — chave de assinatura dos tokens JWT.
Segurança: essas duas chaves vivem no .env. Não commite o .env (já está no .gitignore, confirme). Cada ambiente — dev, staging, produção — gera as suas. Se vazaram, regere imediatamente (e invalide tokens emitidos).

## 7. Rodar migrations e seed

docker compose exec app php artisan migrate --seed
Isso cria as tabelas (users, products, personal_access_tokens, sessions, cache, jobs) e popula com:

2 usuários: admin@globalsys.com.br / ChangeMe!2026 e operator@globalsys.com.br / Operator!2026
10 users sintéticos
23 produtos sintéticos
Aviso: as senhas do seed estão em texto claro no DatabaseSeeder.php — uso dev only. Em homologação ou produção, troque por variáveis de ambiente ou registro manual.

## 8. Sincronizar com a Fake Store (opcional)
Os 23 produtos do seed são sintéticos. Pra ver dados reais da Fake Store:

docker compose exec app php artisan tinker
Dentro do tinker:

app(\App\Services\ProductSyncService::class)->sync();
Ou via API (precisa logar primeiro como admin e pegar token — ver Passo 9).

## 9. Validar — três testes rápidos
Frontend:
Abra http://localhost/ no navegador. Deve cair na tela de login. Entre com admin@globalsys.com.br / ChangeMe!2026.

API direto via curl:

curl -s -X POST http://localhost/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"admin@globalsys.com.br","password":"ChangeMe!2026"}'
Deve retornar JSON com token começando com eyJ... (JWT).

Vite (HMR no dev):

docker compose logs node | tail
Deve ver VITE v7.x ready in ... e estar escutando em 0.0.0.0:5173.

## 10. Comandos do dia-a-dia (cole no README)

###  Acompanhar logs em tempo real
docker compose logs -f app
docker compose exec app tail -f storage/logs/laravel.log

###  Limpar todos os caches do Laravel
docker compose exec app php artisan optimize:clear

###  Listar rotas da API
docker compose exec app php artisan route:list --path=api

###  Reset total do banco (apaga e recria)
docker compose exec app php artisan migrate:fresh --seed

# Lint PHP (formata o código)
docker compose exec app ./vendor/bin/pint

# Subir/parar tudo
docker compose up -d
docker compose down
