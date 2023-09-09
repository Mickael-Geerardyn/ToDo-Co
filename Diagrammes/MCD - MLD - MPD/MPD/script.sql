create table user
(
    id       int auto_increment
        primary key,
    username varchar(25) not null,
    password varchar(64) not null,
    email    varchar(60) not null,
    roles    json        not null,
    constraint UNIQ_8D93D649E7927C74
        unique (email),
    constraint UNIQ_8D93D649F85E0677
        unique (username)
)
    collate = utf8mb4_unicode_ci;

create table task
(
    id         int auto_increment
        primary key,
    user_id    int          null,
    created_at datetime     not null,
    title      varchar(255) not null,
    content    longtext     not null,
    is_done    tinyint(1)   not null,
    constraint FK_527EDB25A76ED395
        foreign key (user_id) references user (id)
)
    collate = utf8mb4_unicode_ci;

create index IDX_527EDB25A76ED395
    on task (user_id);

