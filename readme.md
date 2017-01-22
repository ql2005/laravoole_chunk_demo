为 <https://github.com/garveen/laravoole/issues/16#issuecomment-274311086> issues 写的demo

```bash
    composer create-project laravel/laravel laravoole_chunk_demo  --prefer-dist "5.3.*"
    composer require pion/laravel-chunk-upload
    composer require garveen/laravoole
```

简单起见, 代码都在 `UploadController`
前端直接webuploader 扒的

/upload  访问

附nginx

```nginx
server {
    listen       80;
    server_name  laravoole_demo.com;

    set $root "xxx/wwwroot/laravoole_chunk_demo/public";
    root $root;

    index  index.html index.htm index.php;

    # try_files $uri $uri/ @rewrite;
    # try_files $uri $uri/ @laravooleHttp;
    try_files $uri $uri/ @laravooleCGI;

    location @rewrite {
        rewrite ^/(.*)$ /index.php?_url=/$1;
    }

    # location

    # http OK
    location @laravooleHttp {
        proxy_set_header   Host $host:$server_port;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_http_version 1.1;

        proxy_pass http://127.0.0.1:9050;
    }

    # fastcgi

    location @laravooleCGI {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9050;
    }

    location ~ \.php$ {
        fastcgi_intercept_errors on;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $root$fastcgi_script_name;
        include        /usr/local/etc/tengine/fastcgi_params;

        fastcgi_connect_timeout 1800;
        fastcgi_send_timeout 1800;
        fastcgi_read_timeout 1800;
        fastcgi_buffer_size 1024k;
        fastcgi_buffers 32 1024k;
        fastcgi_busy_buffers_size 2048k;
        fastcgi_temp_file_write_size 2048k;
    }

    location ~* ^/(css|img|js|flv|swf|download)/(.+)$ {
        root $root;
    }
}
```
