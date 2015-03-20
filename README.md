SHOP BÁN ĐỒ GỖ
=======================

Giới thiệu
------------
Shop bán đồ gỗ phát triển từ Zend Framework 2

Copyright 2015 bởi **BichPhuongUITTeam**.

Cài đặt
------------

Dùng Git (khuyên dùng)
----------------------------
<!-- Sử dụng `composer` để cài đặt nhanh hơn so với thông thường.

Dùng  `composer` để cài đặt các gói phụ thuộc, dùng lệnh `create-project`:

    curl -s https://getcomposer.org/installer | php --
    php composer.phar create-project -sdev --repository-url="https://packages.zendframework.com" zendframework/skeleton-application path/to/install -->

Cài đặt Git (hướng dẫn cho Ubuntu)

    sudo apt-get install git

Đối với Windows bạn có thể sử dụng **GitHub** hoặc **SourceTree**.

Clone source về máy tính và sử dụng `composer` để cập nhật các gói phụ trợ dùng file `composer.phar` đã có sẵn trong source:

    cd my/project/dir
    git clone git://github.com/BichPhuongUITTeam/shop-ban-do-go.git
    cd shop-ban-do-go
    php composer.phar self-update
    php composer.phar install

(`self-update` dùng để cập nhật `composer` (file `composer.phar`)).

### Cấu hình kết nối database cho ứng dụng

Tạo mới file `config/autoload/local.php` và copy nội dụng từ fỉle `config/autoload/local.php.dist` sang.

Cấu hình lại các thông số trong file `local.php`: `dbname`, `host`, `username`, `password` cho phù hợp.

### Cấu hình debug tool cho ứng dụng

Mở file `config/application.config.php`.

Uncomment hoặc thêm vào như sau:

    'modules' => array(
        'Application',
        'ZendDeveloperTools',
        'BjyProfiler',
    ),

Copy file `bjyprofiler.local.php` và `zenddevelopertools.local.php` trong folder `resources\local_files` vào folder `config\autoload`.

### Cấu hình domain ảo trong Apache

 Mở file `apache/conf/extra/httpd-vhosts.conf` và thêm vào:

    <VirtualHost *:80>
        ServerName shopdogo.dev
        DocumentRoot /path/to/shop-ban-go-go/public
        SetEnv APPLICATION_ENV "development"
        <Directory /path/to/shop-ban-go-go/public>
            DirectoryIndex index.php
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
    </VirtualHost>

Chắc chắn đã bỏ comment dòng:

    NameVirtualHost *:80

Chạy domain `http://shopdogo.dev`. Nếu bị lỗi 403 thì thêm vào dưới dòng `Allow from all`:

    Require all granted
