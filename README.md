# WB API Data Importer

Laravel-приложение для импорта данных из WB API и сохранения в базу данных.

## Стек технологий

- PHP 8.2
- Laravel 11
- MySQL (freedb.tech) — внешняя БД для хранения данных

## API Источник

- **Хост:** `http://109.73.206.144:6969`
- **Ключ:** `E6kUTYrYwZq2tN4QEtyzsbEBk3ie`

### Эндпоинты

| Эндпоинт | Параметры | Описание |
|-----------|-----------|----------|
| `GET /api/sales` | dateFrom, dateTo | Продажи |
| `GET /api/orders` | dateFrom, dateTo | Заказы |
| `GET /api/stocks` | dateFrom | Склады (только текущий день) |
| `GET /api/incomes` | dateFrom, dateTo | Доходы |

## Доступы к базе данных

| Параметр | Значение |
|----------|----------|
| **Тип БД** | MySQL |
| **Хост** | `sql.freedb.tech` |
| **Порт** | 3306 |
| **Пользователь** | `freedb_elmi_user` |
| **Пароль** | `ma$rhK57Jp!q?fu` |
| **База данных** | `freedb_elmi_database` |

### Подключение

```bash
mysql -h sql.freedb.tech -P 3306 -u freedb_elmi_user -p'ma$rhK57Jp!q?fu' freedb_elmi_database
```

## Таблицы БД

### sales (Продажи) — 115 208 записей
| Колонка | Тип |
|---------|-----|
| id | bigint (PK) |
| g_number | varchar |
| date | date |
| last_change_date | date |
| supplier_article | varchar |
| tech_size | varchar |
| barcode | bigint |
| total_price | decimal(12,2) |
| discount_percent | integer |
| is_supply | boolean |
| is_realization | boolean |
| promo_code_discount | decimal(12,2) |
| warehouse_name | varchar |
| country_name | varchar |
| oblast_okrug_name | varchar |
| region_name | varchar |
| income_id | bigint |
| sale_id | varchar |
| odid | varchar |
| spp | decimal(10,2) |
| for_pay | decimal(12,2) |
| finished_price | decimal(12,2) |
| price_with_disc | decimal(12,2) |
| nm_id | bigint |
| subject | varchar |
| category | varchar |
| brand | varchar |
| is_storno | boolean |

### orders (Заказы) — 125 512 записей
| Колонка | Тип |
|---------|-----|
| id | bigint (PK) |
| g_number | varchar |
| date | datetime |
| last_change_date | date |
| supplier_article | varchar |
| tech_size | varchar |
| barcode | bigint |
| total_price | decimal(12,2) |
| discount_percent | integer |
| warehouse_name | varchar |
| oblast | varchar |
| income_id | bigint |
| odid | varchar |
| nm_id | bigint |
| subject | varchar |
| category | varchar |
| brand | varchar |
| is_cancel | boolean |
| cancel_dt | datetime |

### stocks (Склады) — 3 288 записей
| Колонка | Тип |
|---------|-----|
| id | bigint (PK) |
| date | date |
| last_change_date | date |
| supplier_article | varchar |
| tech_size | varchar |
| barcode | bigint |
| quantity | integer |
| is_supply | boolean |
| is_realization | boolean |
| quantity_full | integer |
| warehouse_name | varchar |
| in_way_to_client | integer |
| in_way_from_client | integer |
| nm_id | bigint |
| subject | varchar |
| category | varchar |
| brand | varchar |
| sc_code | bigint |
| price | decimal(12,2) |
| discount | integer |

### incomes (Доходы) — 3 270 записей
| Колонка | Тип |
|---------|-----|
| id | bigint (PK) |
| income_id | bigint |
| number | varchar |
| date | date |
| last_change_date | date |
| supplier_article | varchar |
| tech_size | varchar |
| barcode | bigint |
| quantity | integer |
| total_price | decimal(12,2) |
| date_close | date |
| warehouse_name | varchar |
| nm_id | bigint |

## Установка и запуск

```bash
composer install

cp .env.example .env

php artisan migrate

php artisan api:import all

php artisan api:import sales --dateFrom=2024-01-01 --dateTo=2026-12-31
php artisan api:import orders --dateFrom=2024-01-01 --dateTo=2026-12-31
php artisan api:import stocks
php artisan api:import incomes --dateFrom=2024-01-01 --dateTo=2026-12-31
```

## Структура проекта

```
wb-api/
├── app/
│   ├── Console/Commands/
│   │   └── ImportApiData.php     # Artisan-команда импорта
│   └── Models/
│       ├── Sale.php
│       ├── Order.php
│       ├── Stock.php
│       └── Income.php
├── config/
│   └── services.php              # Конфигурация WB API
├── database/
│   └── migrations/               # Миграции для 4 таблиц
└── .env                          # Переменные окружения
```
