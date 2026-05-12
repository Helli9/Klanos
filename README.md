# myApp — Guild Need List Manager

A PHP MVC web application for managing guild item need lists, built for games with PvP and PvE progression systems.

## Features

- User authentication (signup, login, logout)
- Session-based security with 30-minute idle timeout
- PvP and PvE need list management per user
- Item catalog browsed by category
- Rate-limited login (5 attempts before 15-minute lockout)
- Dockerised development environment (PHP-FPM, Nginx, MySQL)

## Tech Stack

- **Backend** — PHP 8.x, custom MVC framework (no external framework)
- **Database** — MySQL 8
- **Web server** — Nginx
- **Environment** — Docker + Docker Compose
- **Password hashing** — Argon2id
- **Security** — CSRF tokens, prepared statements, session hardening, global error handler

## Project Structure

```
myApp/
├── App/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   └── NeedListController.php
│   ├── Core/
│   │   ├── Controller.php
│   │   └── ErrorHandler.php
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── NeedListModel.php
│   │   └── ItemModel.php
│   └── Views/
│       ├── layout/
│       │   └── home.php
│       └── pages/
│           ├── dashboard.php
│           ├── login.php
│           ├── need_lists.php
│           └── signup.php
├── Config/
│   ├── bootstrap.php
│   ├── database.php
│   └── session_check.php
├── Docker/
│   ├── mysql/
│   │   └── init.sql
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       ├── fpm.conf
│       └── php.ini
├── Public/
│   ├── css/
│   └── index.php
├── routes/
│   └── web.php
├── storage/
│   └── logs/
├── .gitattributes
└── docker-compose.yml
```

## Getting Started

### Requirements

- [Docker](https://www.docker.com/) and Docker Compose

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Helli9/Klanos.git
   cd Klanos
   ```

2. Copy the environment file and fill in your values:
   ```bash
   cp .env.example .env
   ```

3. Start the containers:
   ```bash
   docker compose up -d
   ```

4. The app will be available at `http://localhost`.

### Environment Variables

Create a `.env` file in the project root:

```env
DB_HOST=mysql
DB_NAME=users
DB_USER=root
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4
```

> **Never commit `.env` to version control.** It is listed in `.gitignore`.

## Security

- CSRF tokens on every POST route
- Argon2id password hashing
- Session fixation protection via `session_regenerate_id()` on login
- `SameSite=Strict`, `HttpOnly` session cookies
- Login rate limiting — 5 failed attempts triggers a 15-minute lockout
- Whitelisted view paths and tab parameters — no path traversal possible
- Global exception handler — errors are logged privately, never exposed to the browser
- All user output escaped with `htmlspecialchars()` via the `e()` helper

## License

MIT
