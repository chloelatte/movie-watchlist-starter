# FilmLog — Movie Watchlist Application

FilmLog is a movie watchlist application that helps users discover movies, organize their watchlists, track watched movies, and receive AI-powered movie recommendations based on a local movie database.

This project was developed as part of the **Backend Technical Assignment for the Google Developer Groups on Campus (GDGoC) UGM recruitment process**.

## Features

- **Movie Discovery** — Browse, search, and filter movies from the movie catalog.
- **Movie Details** — View movie information, including title, release year, genres, director, synopsis, and rating.
- **User Authentication** — Register and log in using JWT-based authentication.
- **Personal Watchlist** — Add and remove movies from a personal watchlist.
- **Watched Movies** — Mark movies as watched and submit ratings and reviews.
- **AI Movie Recommendations** — Get movie recommendations from Google Gemini based on a user prompt and the local movie database.
- **Statistics** — View personal movie-watching statistics.
- **API Inspector** — Inspect API requests and responses through the frontend.

## Tech Stack

| Category | Technologies |
|---|---|
| Backend | PHP, Laravel 13 |
| Database | MySQL |
| Authentication | JWT Auth |
| Frontend | HTML, Tailwind CSS, Vanilla JavaScript |
| AI Integration | Google Gemini API |
| Development Tools | Composer, Vite, Python HTTP Server |

## Project Structure

```text
movie-watchlist-starter/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/
│   │   └── Models/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   │   └── api.php
│   ├── tests/
│   ├── .env.example
│   ├── composer.json
│   └── package.json
├── data/
│   ├── movies.json
│   └── seed.sql
├── docs/
│   ├── DATABASE.md
│   ├── filmlog-erd.webp
│   └── filmlog_schema.sql
├── frontend/
│   ├── index.html
│   ├── app.js
│   └── README.md
└── README.md
```

## Requirements

Make sure the following tools are installed:

- PHP 8.3 or later
- Composer
- MySQL 8 or compatible version
- Python 3
- Git

A Google Gemini API key is required to use the AI recommendation feature.

## Installation and Setup

### 1. Clone the Repository

```bash
git clone https://github.com/chloelatte/movie-watchlist-starter.git
cd movie-watchlist-starter
```

### 2. Set Up the Backend

Navigate to the backend directory:

```bash
cd backend
```

Install PHP dependencies:

```bash
composer install
```

Create the environment configuration file:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Generate the JWT secret:

```bash
php artisan jwt:secret
```

### 3. Configure Environment Variables

Open `backend/.env` and configure the following variables according to your local environment:

```env
APP_NAME=FilmLog
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=movie_watchlist
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

GEMINI_API_KEY=your_gemini_api_key
```

Replace `your_mysql_password` with your own MySQL password.

Replace `your_gemini_api_key` with your Google Gemini API key. The key can be obtained from [Google AI Studio](https://aistudio.google.com/apikey).

**Important:** Never commit your `.env` file or expose your API keys and other credentials in a public repository.

### 4. Create the Database

Create a MySQL database named:

```sql
CREATE DATABASE movie_watchlist;
```

You can create the database using phpMyAdmin or a MySQL client.

### 5. Run Migrations and Seeders

Make sure the database configuration is correct, then run:

```bash
php artisan migrate
```

Populate the movie catalog using the database seeder:

```bash
php artisan db:seed
```

The seeder executes `MovieSeeder` through `DatabaseSeeder`.

**Note:** Run the seeder only once on a fresh database. Running it again on an already populated database may cause duplicate primary key errors because the movie records use predefined IDs.

### 6. Start the Backend Server

```bash
php artisan serve
```

The backend API will be available at:

```text
http://127.0.0.1:8000
```

### 7. Start the Frontend

Open a **new terminal** and navigate to the frontend directory from the project root:

```bash
cd frontend
```

Start the local development server:

```bash
python -m http.server 3000
```

Open the application in your browser:

```text
http://localhost:3000
```

The frontend communicates with the backend API at `http://localhost:8000/api` by default. The API base URL can also be configured through the frontend Settings.

## API Documentation

Base URL: 
```text
http://localhost:8000/api
```
All endpoints listed below are relative to the API Base URL.

### Authentication

| Method | Endpoint | Description | Authentication |
|---|---|---|---|
| POST | `/auth/register` | Register a new account | No |
| POST | `/auth/login` | Authenticate a user and obtain a JWT | No |

Example registration request:

```json
{
  "username": "john",
  "email": "john@example.com",
  "password": "password123"
}
```

Example login request:

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Movies

| Method | Endpoint | Description | Authentication |
|---|---|---|---|
| GET | `/movies` | Retrieve the movie catalog | No |
| GET | `/movies/{id}` | Retrieve movie details | No |
| POST | `/movies/recommend` | Generate an AI movie recommendation | No |

The movie catalog endpoint supports the following optional query parameters:

| Parameter | Description | Example |
|---|---|---|
| `search` | Search movies by title | `/api/movies?search=inception` |
| `genre` | Filter movies by genre | `/api/movies?genre=Sci-Fi` |
| `random` | Randomize the order of movies | `/api/movies?random=true` |

Multiple query parameters can also be combined.

Example:

```http
GET /api/movies?genre=Sci-Fi&search=star&random=true
```

Example AI recommendation request:

```json
{
  "prompt": "Recommend a movie about friendship and personal growth."
}
```

The recommendation endpoint uses Google Gemini to select a movie from the local movie catalog. The returned movie ID is validated against the database before the response is sent.

### Watchlist

| Method | Endpoint | Description | Authentication |
|---|---|---|---|
| GET | `/watchlist` | Retrieve the authenticated user's watchlist | JWT |
| POST | `/watchlist` | Add a movie to the watchlist | JWT |
| DELETE | `/watchlist/{movieId}` | Remove a movie from the watchlist | JWT |

Example request to add a movie:

```json
{
  "movieId": 1
}
```

### Watched Movies

| Method | Endpoint | Description | Authentication |
|---|---|---|---|
| GET | `/watched` | Retrieve watched movies and reviews | JWT |
| POST | `/watched` | Mark a movie as watched and submit a review | JWT |

Example request:

```json
{
  "movieId": 1,
  "rating": 5,
  "review": "An excellent movie!"
}
```

The rating must be an integer between 1 and 5.

### Statistics

| Method | Endpoint | Description | Authentication |
|---|---|---|---|
| GET | `/statistics` | Retrieve personal movie-watching statistics | JWT |

### Authentication Header

Protected endpoints require a valid JWT in the request header:

```http
Authorization: Bearer YOUR_JWT_TOKEN
Accept: application/json
```

## Testing

FilmLog includes automated feature tests to verify API functionality, authentication, input validation, and Gemini recommendation behavior.

To run the automated tests, navigate to the backend directory:

```bash
cd backend
```

Then execute:

```bash
php artisan test
```

The test suite uses an in-memory SQLite database, keeping automated tests separate from the local MySQL development database.

Gemini API requests are mocked during testing, so a valid Gemini API key and an active internet connection are not required to run the automated test suite.

The test suite currently contains 25 tests (23 application-specific feature tests and 2 default Laravel tests), with 69 assertions. All tests passed in the latest run.

The application-specific tests cover:

- Movie listing, searching, filtering, and details.
- User registration and login.
- JWT-protected watchlist operations.
- Watched movies and review validation.
- Personal movie statistics.
- Gemini recommendation responses and local catalog validation.

## Database Documentation

FilmLog uses a relational MySQL database consisting of four main tables:

| Table | Description |
|---|---|
| `users` | Stores registered user accounts |
| `movies` | Stores the movie catalog |
| `watchlists` | Stores movies added to individual users' watchlists |
| `reviews` | Stores watched-movie ratings and reviews |

Each user's watchlist and watched-movie records are associated with their account, ensuring that personal data is scoped to the authenticated user.

For more details, refer to:

- [Database Documentation](docs/DATABASE.md)
- [Database Schema SQL](docs/filmlog_schema.sql)
- [Entity Relationship Diagram](docs/filmlog-erd.webp)

## Frontend Documentation

For additional frontend information and implementation details, refer to [Frontend README](frontend/README.md).

## Security Considerations

- User authentication is implemented using JSON Web Tokens (JWT).
- Protected endpoints require authentication.
- Watchlist and watched-movie data are retrieved within the authenticated user's scope.
- Request validation is implemented for registration, login, watchlist operations, and reviews.
- Gemini API credentials are stored in environment variables rather than hardcoded in the source code.
- AI-generated movie recommendations are validated against the local movie catalog.

## Project Context and Credits

This project was developed as part of the Backend Technical Assignment for the Google Developer Groups on Campus (GDGoC) UGM recruitment process.

- **Starter Repository:** [bimoar07/movie-watchlist-starter](https://github.com/bimoar07/movie-watchlist-starter)
- **Project Implementation and Customization:** [chloelatte](https://github.com/chloelatte)