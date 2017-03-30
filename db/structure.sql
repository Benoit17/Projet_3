drop table if exists t_comment;
drop table if exists t_billet;

create table t_billet (
    billet_id integer not null primary key auto_increment,
    billet_title varchar(100) not null,
    billet_content varchar(2000) not null
) engine=innodb character set utf8 collate utf8_unicode_ci;

create table t_comment (
    com_id integer not null primary key auto_increment,
    com_author varchar(100) not null,
    com_content varchar(500) not null,
    billet_id integer not null,
    constraint fk_com_billet foreign key(billet_id) references t_billet(billet_id)
) engine=innodb character set utf8 collate utf8_unicode_ci;