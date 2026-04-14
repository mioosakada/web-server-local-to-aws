# Web Server: From Local to AWS Deployment

## Overview
This project demonstrates building a web server locally and deploying it to AWS.


## Development Environment
- OS: Ubuntu 24.04.4 LTS  
  Check command:
  ```bash
  cat /etc/os-release
  ```
- Web Server: Nginx
- Database: MySQL 8.0.45  
  Check command:
  ```bash
  mysql --version
  ```


## Steps
### 0. Checking the Current User
```bash
whoami
```
▶︎ 実行中のユーザーを確認することで権限や実行環境を把握し、適切なコマンド（sudoの必要性）などを判断する

### 1. System Update
```bash
sudo apt update
sudo apt upgrade -y
sudo apt autoremove -y
```
- `apt update`
  　パッケージ一覧（リポジトリ情報）を更新
- `apt upgrade -y`
  　インストール済みパッケージを更新
- `apt autoremove -y`
  　不要になった依存パッケージを削除

▶︎ システムの脆弱性や不具合を防ぐため、事前にパッケージを最新化してシステムを安全な状態にする


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
sudo mysql_secure_installation
sudo mysql
```
- `apt install mysql-server -y`
  　MySQLをインストール
- `systemctl status mysql`
  　起動確認
- `mysql_secure_installation`
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
