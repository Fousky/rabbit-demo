# RabbitMQ demo

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fousky/rabbit-demo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fousky/rabbit-demo/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/fousky/rabbit-demo/badges/build.png?b=master)](https://scrutinizer-ci.com/g/fousky/rabbit-demo/build-status/master)

## Minimální požadavky

* PHP >= 7.0.8

## Instalace

### 1. naklonujeme z repozitáře

Předpokladem je, že máme globálně [nainstalovaný GIT](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

    git clone https://github.com/fousky/rabbit-demo.git rabbit-demo

### 2. nainstalujeme závislosti

Předpokladem je, že máme globálně [nainstalovaný Composer](https://getcomposer.org/download/).

    composer install

Po instalaci závislostí už můžeme aplikaci procházet, jen nám nebude fungovat žádné spojení s RabbitMQ nebo Management API.

Použité knihovny:
* `bunny/bunny` komunikuje přímo s RabbitMQ nativním protokolem
* `richardfullmer/rabbitmq-management-api` komunikuje s RabbitMQ / Management API a umožňuje nastavovat Virtual Hosts, Queues apod.
* `php-http/guzzle6-adapter` potřebná závislost pro `richardfullmer/rabbitmq-management-api` 
* vše ostatní je součást frameworku Symfony nebo nástroje pro vývoj (coding-standards, lintery, PhpStan apod.)

### 3. lokálně nainstalujeme RabbitMQ

Přes [oficiální návod](https://www.rabbitmq.com/download.html) nainstalujeme RabbitMQ - k dispozici jsou instalátory pro nejpoužívanější OS (Windows, Linux, MacOS)

### 4. nastavíme lokální RabbitMQ

Zapneme modul `rabbitmq_management` přes [oficiální návod](https://www.rabbitmq.com/management.html).

### 5. nastavíme admin uživatele

z CLI spustíme následující příkazy (pod sudo uživatelem / administrátorem)

* `rabbitmqctl add_user {username} {password}`
* `rabbitmqctl set_user_tags {username} administrator`
* `rabbitmqctl set_permissions -p / {username} ".*" ".*" ".*"`

### 6. zkontrolujeme RabbitMQ webové rozhraní

Přejdeme na [lokální URL](http://127.0.0.1:15672/) a přihlásíme se. 
Volitelně si můžeme nastavit v lokálním serveru (Apache, Nging, IIS) ProxyPass, aby se nám RabbitMQ zobrazoval pod vlastní URL, např. [http://rabbit.localhost](http://rabbit.localhost) apod.

### 7. nastavíme parametry pro připojení aplikace k RabbitMQ

Uvnitř souboru `.env` upravíme parametry začínající `RABBIT_*`. Pokud nám soubor `.env` chybí, zkopírujeme ho ze souboru `.env.dist`

Pokud nám spojení funguje, pak následující příkaz neodpoví chybou:

    php bin/console rabbit:test

## Dostupné CLI commandy

Po úspěšné instalaci máme k dispozici následující příkazy, které se dají spouštět z příkazové řádky.

### `php bin/console rabbit:create:virtualhost`
Příkaz pro vytvoření nového "prostředí" (VirtualHost) v RabbitMQ. [Více informací zde](https://www.rabbitmq.com/vhosts.html).

### `php bin/console rabbit:delete:virtualhost`
Příkaz pro smazání "prostředí" (VirtualHost).

### `php bin/console rabbit:create:queue`
Příkaz pro vytvoření nové "fronty" (Queue) v RabbitMQ. [Více informací zde](https://www.rabbitmq.com/queues.html).

### `php bin/console rabbit:delete:queue`
Příkaz pro smazání "fronty" (Queue).

### `php bin/console rabbit:create:user`
Příkaz pro vytvoření RabbitMQ uživatele - **funguje jen na Linuxu**.

### `php bin/console rabbit:create:binding`
Příkaz pro vytvoření nové "vazby" (Binding) mezi Exchange a Queue.

### `php bin/console rabbit:delete:binding`
Příkaz pro smazání "vazby" (Binding).

### `php bin/console rabbit:test`
Příkaz pro testování spojení s RabbitMQ Management API.


### `php bin/console rabbit:produce:email`
Příkaz, který produkuje určité množství "zpráv" (Messages) na určitého pošťáka (Exchange).

### `php bin/console rabbit:consume:email`
Příkaz, který naslouchá na určitou "frontu" (Queue) a umí "konzumovat" (Consume) zprávy - jen zobrazuje jejich množství.
