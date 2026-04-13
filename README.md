# Web Server: From Local to AWS Deployment

## Overview
This project demonstrates building a web server locally and deploying it to AWS.  
  
## Steps
### 1. System Update
システムを最新かつ安全な状態にする
```bash
sudo apt updaye
sudo apt upgrade -y
```
- `apt update`
  　パッケージ一覧を更新
- `apt upgrade -y`
  　インストール済みパッケージを更新

システムの脆弱性や不具合を防ぐため、事前にパッケージ更新を行う


### 2. Web Server Setup
```bash
sudo apt install nginx -y
sudo systemctl status nginx
sudo systemctl enable nginx
sudo vi /var/www/html/index.html
```
- `apt install nginx -y`
  　WebサーバーソフトのNginxをインストール
- `systemctl status nginx`
  　起動確認
- `systemctl enable nginx`
  　自動起動をONにする
- `vi /var/www/html/index.html`
  　HTMLファイルを作成・編集
  ```html
  <h1>Hello World</h1>
  ```

ブラウザで http://IPアドレス にアクセスすると、Nginxは設定ファイルに従ってWebコンテンツを取得して表示する。  
デフォルト設定では、/var/www/html 配下の index.html が指定されている。  
Nginx設定ファイル：/etc/nginx/sites-available/default
```nginx
root /var/www/html;
index index.html;
```


### 3. MySQL Setup
#### 3.1 MySQL Installation and Login
```bash
sudo apt install mysql-server -y
sudo systemctl status mysql
sudo mysql_sequre_installation
sudo mysql
```
- `apt install mysql-server -y`
  　MySQLをインストール
- `systemctl status mysql`
  　起動確認
- `mysql_sequre_installation`
  　初期設定：不要なユーザーやテストDBを削除
- `mysql`
  　SQLにログイン

インストール直後に自動で作成される匿名ユーザーは、ユーザー名なしで誰でもログインできるためセキュリティ的に危険である。  
テストDBは権限が緩く、他のDB情報を取得されるリスクがある。

#### 3.2 Database Setup
```sql
CREATE DATABASE portfolio_db;
USE portfolio_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  email VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, email)
VALUES ('Mio Osakada', 'mioosakada@example.com');

SELECT * FROM users;

CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(100),
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO posts (user_id, title, content)
VALUES (1, 'First Post', 'This is my first post');

SELECT * FROM users;
SELECT * FROM posts;

SELECT users.name, posts.title
FROM users
JOIN posts ON users.id = posts.user_id;
```
