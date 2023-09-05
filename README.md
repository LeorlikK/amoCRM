# amoCRM

## Account:
- login=leorl1k@yandex.ru
- password=pqsd137zh12e9e

## Routes:
- **WebHook**: /api/change-lead(POST)
- **Authorization**: /authorization(GET/POST)

## Services:
- **AmoService** - основная логика расчёта сделки.
- **AmoLeadService** - сохраняет значения сделки, чтобы избежать зацикливания.
- **AmoTokenService** - получение, сохранение, обновление access_token.
