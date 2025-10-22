<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

---

## 🧩 Задача

Вам необходимо **стянуть все данные по описанным эндпоинтам** и сохранить их в базу данных.

Используется **Laravel** и **MySQL**.

- Код проекта необходимо выложить в **Git**
- Базу данных развернуть на любом **бесплатном хостинге**
- Предоставить **доступы к БД** и **названия таблиц**
- Время на выполнение: **3 дня**

---

## 🗄️ Данные БД

| Параметр | Значение |
|-----------|-----------|
| **Хост** | `10.0.0.110` |
| **База данных** | `a1183197_api` |
| **Пользователь** | `a1183197_api` |
| **Пароль** | `tPVovSLm` |

### Таблицы:
- `stocks`
- `incomes`
- `sales`
- `orders`

---

## 🚀 Импорт данных

Импорт выполняется через artisan-команду:

```bash
php artisan app:import-api-data --type=<тип> --dateFrom=<дата> --dateTo=<дата>
```

#### Доступные типы:
- `stocks` — остатки
- `incomes` — поступления
- `sales` — продажи
- `orders` — заказы

#### Примеры:

Импорт продаж за период:
```bash
php artisan app:import-api-data --type=sales --dateFrom=2025-10-01 --dateTo=2025-10-22
```

Импорт остатков:
```bash
php artisan app:import-api-data --type=stocks
```

---

## ⚙️ Установка и запуск

### Установка зависимостей PHP

```bash
composer install
```

### Генерация ключа приложения

```bash
php artisan key:generate
```

### Запуск воркера Laravel

```bash
php artisan queue:work
```

---

## 📚 Документация и примеры

- Репозиторий с описанием задания:  
  🔗 [https://github.com/cy322666/wb-api](https://github.com/cy322666/wb-api)

- Коллекция Postman с примерами запросов:  
  🔗 [https://www.postman.com/cy322666/workspace/app-api-test/overview](https://www.postman.com/cy322666/workspace/app-api-test/overview)
