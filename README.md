# Girchi.com - გირჩის პორტალი

ეს არის გირჩის პორტალის ვებ-ვერსიის იმპლემენტაცია. პროექტი სრულდება [Drupal 8](https://www.drupal.org/) პლათფორმის გამოყენებით.


## ინსტალაციის ინსტრუქცია

ყურადღება: სანამ ინსტალაციას დაიწყებთ გთხოვთ გაითვალისწინოთ რომ დეველოპმენტისთვის ვიყენებთ Docker კონტეინერებს, შესაბამისად [Docker](https://www.docker.com/) ინსტალაცია პრერეკვიზიტია. 

თუ არ გსურთ Docker - ის გამოყენება მიყევით Drupal 8 - ს [ინსტალაციის სტანდარტულ პროცედურებს](https://www.drupal.org/docs/8/install).

მიმდინარე ვერსიის ინსტალაციის ინსტრუქცია დეველოპერებისთვის: 

1. `git clone git@github.com:Girchi/girchi-com-d8.git`;
1. `cd girchi-com-d8`;
1. `make install` ეს ნაბიჯი მოიცავს შემდეგ ქვე-ნაბიჯებს:

    - `make up` - Docker კონტეინერების შექმნა და გაშვება;
    - `make build-ui` - [Girchi UI](https://github.com/Girchi/girchi-com-ui) პროექტის „გაბილდვა“;
    - `./scripts/pre-install.sh` - წინა საინსტალაციო პროცედურები;
    - `docker-compose exec php composer install` - Composer პაკეტების დაყენება კონტეინერის შიგნით;
    - `docker-compose exec php drush si --existing-config --account-pass=1234  -y -vvv` - Drupal 8 - ის ინსტალაცია არსებული კონფიგ-ფაილებიდან;
    - `docker-compose exec php drush language-import` - თარგმანის იმპორტი;

ინსტალაციის შემდეგ პროექტის უნდა მუშაობდეს შემდეგ მისამართზე: [http://girchi.docker.localhost](http://girchi.docker.localhost)

კონტეინერების სამართავად ვიყენებთ Wodby - ის [Docker4Drupal](https://github.com/wodby/docker4drupal) - ს.

