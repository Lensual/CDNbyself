# CDNbyself

自己写的一些脚本和配置文件，可用于自建CDN。。

api 目前只放了同步请求接口

之后准备实现更多的接口~~，并且nginx配置也会放上来~~

#nginx 配置

设置好 root listen ssl resolver 就可以了

# 同步请求接口

用于接收并执行站点向CDN的发起数据同步请求

使用 rsync+ssh+源站私钥 方式同步

 - `domain` 用于 CDN 辨别站点的域名
 - `addr` 源站 ip 地址或域名，用于连接源站
 - `port` 源站ssh端口，默认22
 - `srcpath` 源站中 rsync 同步的路径
 - `speedlimit` 传输限速 KByte/s
 - `cmd` 仅输出生成的命令，用于排错

Example:
```
http://cdn.example.com/api/update.php?domain=www.mywebsite.com&addr=111.222.0.254&srcpath=/data/wwwroot/www.mywebsite.com&speedlimit=50
```

## PHP

未实现接口授权

参数存在注入漏洞

## CGI（已弃用）

这个cgi脚本在fcgiwrap中执行rsync同步时总是阻塞新的cgi请求，并存在参数注入问题，已用php重构。

# The end

啦啦啦，安全出了问题不负责哦www（x

欢迎提Issues
