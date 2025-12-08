# Project Setup Instructions

Follow the steps below to install and set up the project on your local machine:

## Requirements

Make sure you have the following installed on your system:

- PHP (compatible version with Laravel)
- Composer
- MySQL
- Node.js and npm (for frontend dependencies)

---

## Installation Steps

1. **Clone the repository**  
   Clone the project repository to your local machine:
   ```bash
   git clone <repository-url>
   cd <project-directory>
   ```

2. **Set up the `.env` file**  
   Copy the example `.env` file and configure it according to your environment:
   ```bash
   cp .env.example .env
   ```
   Update the following values in your `.env` file to match your MySQL database credentials:
    - `DB_DATABASE`: The name of the database you create.
    - `DB_USERNAME`: Your MySQL username.
    - `DB_PASSWORD`: Your MySQL password.

3. **Install dependencies**  
   Install PHP and Node.js dependencies:
   ```bash
   composer install
   npm install
   ```

4. **Generate the application encryption key**  
   Run the following command to generate the encryption key:
   ```bash
   php artisan key:generate
   ```

5. **Run database migrations**  
   Migrate the database tables required by the application:
   ```bash
   php artisan migrate
   ```

6. **Seed the database**  
   Populate the database with seed data by running:
   ```bash
   php artisan db:seed
   ```

7. **Fund users' wallets**  
   Execute the seed class to fund user wallets:
   ```bash
   php artisan db:seed --class=FundUserWallets
   ```

8. **Create a symbolic link for storage**  
   Since listing upload handles files, ensure that the required storage link is created:
   ```bash
   php artisan storage:link
   ```

---

## Additional Notes

- Ensure your MySQL server is up and running before proceeding with the migrations or seeds.
- After completing all the above steps, you can run the application locally:
   ```bash
   php artisan serve
   ```
  Access your application at [http://localhost:8000](http://localhost:8000).
- For frontend assets, you might need to run:
   ```bash
   npm run dev
   ```

Now you're all set! 🎉
