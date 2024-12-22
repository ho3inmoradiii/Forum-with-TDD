# Forum With TDD

A modern question and answer forum built with Laravel and Vue.js, following Test-Driven Development practices.

## Overview

Forum With TDD is a community-driven platform where users can:
- Post questions on various topics
- Answer other users' questions
- Engage in discussions through comments
- Vote on helpful answers

## Tech Stack

### Backend
- PHP 8.x
- Laravel Framework
- MySQL
- PHPUnit for testing

### Frontend
- Vue.js
- JavaScript
- HTML/CSS

## Prerequisites

- PHP >= 8.0
- Composer
- Node.js & NPM
- MySQL
- Git

## Installation

1. Clone the repository
```
git clone https://github.com/ho3inmoradiii/Forum-with-TDD.git
cd Forum-with-TDD
```

2. Install PHP dependencies
```bash
composer install
```

3. Install JavaScript dependencies
```bash
npm install
```

4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure your database in `.env` file
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forum_with_tdd
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations
```bash
php artisan migrate
```

7. Compile assets
```bash
npm run dev
```

8. Start the development server
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Testing

This project follows Test-Driven Development practices. To run the tests:

```bash
php artisan test
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License.