create database if not exists projet_3 character set utf8 collate utf8_unicode_ci;
use projet_3;

grant all privileges on projet_3.* to 'projet_3_user'@'localhost' identified by 'secret';