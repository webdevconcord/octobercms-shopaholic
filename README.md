# Модуль ConcordPayShopaholic для October CMS

Для работы модуля у вас должен быть установлен модуль **LOVATA Shopaholic**.

## Установка

1. Содержимое архива поместить в папку плагинов **October CMS** - *({YOUR_SITE}/plugins/concordpay/concordpayshopaholic)*.

2. В админ-разделе сайта зайти в *«Настройки -> Система -> Обновления и плагины»* и активировать плагин **ConcordPay для Shopaholic**.

3. Перейти в *«Настройки -> Конфигурация каталога -> Методы оплаты»* и создать новый метод оплаты.

4. Данные метода оплаты:
    - Название: **ConcordPay**
    - Код: **concordpay**
    - Описание (например): **Оплата Visa, Mastercard, Apple Pay, Google Pay**
    - Платёжная система (выбрать из выпадающего списка): **ConcordPay**
    - Включить переключатель **«Активность»**

5. Сохранить метод оплаты (кнопка *«Создать»*).

6. На появившейся вкладке *«Платёжная система»* заполнить данные вашего продавца.
   - Валюта платёжной системы: **UAH**
   - Указать статусы заказа на различных этапах его существования;
   - Обязательно включить переключатель **«Отправлять запрос в платежный шлюз при создании заказа»**;

   Укажите данные из личного кабинета **ConcordPay**:
   - *Идентификатор продавца (Merchant ID)*;
   - *Секретный код (Secret key)*;

7. Снова сохраните настройки метода оплаты (кнопка *«Сохранить и закрыть»*).

8. К модулю прилагаются файлы темы (каталог **themes** в папке плагина), которые необходимы для отображения сообщений покупателю о результатах оплаты.

В существующем виде их можно использовать с темой **«Bootstrap theme for Shopaholic»** от **LOVATA**.

Для других тем надо файлы из каталога *themes/lovata-bootstrap-shopaholic* поместить в папку с активной темой.

Модуль готов к работе!

*Примечание.*
*Переустановка модуля из командной строки:*

> *php artisan plugin:refresh concordpay.concordpayshopaholic*

*Модуль протестирован для работы с October CMS 1.1.5, Lovata Shopaholic 1.30.1 и PHP 7.4*.
