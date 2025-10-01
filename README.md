# Sistema-M2P

## Descrição
Sistema web para gestão de usuários e veículos em uma oficina mecânica (M2P). Permite cadastro, login, listagem de usuários, área administrativa e área do cliente, com controle de acesso e segurança básica.

## Estrutura de Pastas

```
login/
	app/           # Configuração e conexão com banco de dados
		config.php
	assets/        # Arquivos estáticos (logo, CSS)
		logo.png
		css/
			style.css
			style_user.css
	public/        # Arquivos acessíveis via navegador (rotas principais)
		admin.php
		index.php
		listar_usuarios.php
		login.php
		logout.php
		register.php
		salvar_usuario.php
		user.php
	sql/
		schema.sql   # Script de criação do banco de dados
```

## Funcionalidades

- **Cadastro de Usuário:**
	- Nome, telefone, CPF, placa do veículo (com hash para segurança)
	- Validação de campos e tratamento de erros
- **Login:**
	- Autenticação por CPF e placa (hash)
	- Bloqueio temporário após 5 tentativas erradas de 30 segundos
- **Listagem de Usuários:**
	- Apenas para usuários autenticados
	- Exibe dados básicos dos usuários cadastrados
- **Área do Administrador:**
	- Acesso restrito ao tipo `admin`
	- Visualização de informações administrativas
- **Área do Cliente:**
	- Acesso restrito ao tipo `user`
	- Exibe dados simulados do veículo, serviços, orçamento e histórico
- **Logout:**
	- Encerra a sessão de forma segura

## Tecnologias Utilizadas

- PHP 
- MySQL
- HTML5, CSS3, JavaScript, PHP
- PDO para acesso ao banco de dados
- XAMPP 

## Como rodar o projeto localmente

1. Instale o XAMPP e inicie Apache e MySQL.
2. Clone ou copie o projeto para a pasta `htdocs`.
3. Importe o arquivo `login/sql/schema.sql` no phpMyAdmin para criar o banco e a tabela `usuarios`.
4. Ajuste as configurações de acesso ao banco em `login/app/config.php` se necessário.
5. Acesse no navegador:
	 - `http://localhost/Mecanica%20-%20Saas/Sistema-M2P/login/public/index.php`

## Usuários de exemplo

O banco já vem com dois usuários:

- **Admin:**
	- CPF: 123.456.789-00
	- Placa: ABC-1234
- **Usuário comum:**
	- CPF: 987.654.321-00
	- Placa: ABC-5678

## Observações de Segurança

- As senhas (placa) são armazenadas com hash seguro (bcrypt).
- O sistema utiliza sessões para controle de login.
- Não exponha dados sensíveis na URL.
- Recomenda-se usar HTTPS em produção.

## Melhorias Futuras

- Implementar recuperação de senha
- CRUD completo de veículos e serviços
- Painel administrativo mais detalhado
- Logs de acesso e auditoria
- Testes automatizados

---
Desenvolvido por Marco Antonio, Pedro Faria e Pedro Miranda.