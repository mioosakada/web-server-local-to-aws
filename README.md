# Web Server: From Local to AWS Deployment

## Overview
This project demonstrates building a web server locally and deploying it to AWS.


## Development Environment
- OS: Ubuntu 24.04.4 LTS  
  Check Command:
  ```bash
  cat /etc/os-release
  ```
- Web Server: Nginx
- Database: MySQL 8.0.45  
  Check Command:
  ```bash
  mysql --version
  ```


## Steps
### 0. Check　Current User
```bash
whoami
```
▶︎ 実行中のユーザーを確認することで権限や実行環境を把握し、適切なコマンド（sudoの必要性）などを判断する

### 1. Update　System
```bash
sudo apt update
sudo apt upgrade -y
sudo apt autoremove -y
```
- `apt update`
  　パッケージ一覧（リポジトリ情報）を更新する
- `apt upgrade -y`
  　インストール済みパッケージを更新する
- `apt autoremove -y`
  　不要になった依存パッケージを削除する

▶︎ システムの脆弱性や不具合を防ぐため、事前にパッケージを最新化してシステムを安全な状態にする


### 2. Set Up Web Server
```bash
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
sudo systemctl status nginx
sudo vi /var/www/html/index.html
```
- `apt install nginx -y`
  　WebサーバーソフトのNginxをインストールする
- `systemctl start nginx`
  　Nginxを起動する
- `systemctl enable nginx`
  　自動起動を有効化する
- `systemctl status nginx`
  　サービスの起動状態を確認する
- `vi /var/www/html/index.html`
  　HTMLファイルを作成する
  ```html
  <h1>Hello World</h1>
  ```
動作確認：  
ブラウザで http://IPアドレス にアクセスし、作成したHTMLが表示されることを確認する  
（Nginxはポート80でHTTP通信を受け付ける）  

Nginxはデフォルト設定で /var/www/html 配下の index.html を表示する  
Nginx設定ファイル：/etc/nginx/sites-available/default
```nginx
root /var/www/html;
index index.html;
```
設定ファイル変更後に実行するコマンド：
```bash
sudo nginx -t
sudo systemctl reload nginx
```
- `nginx -t`
  　設定ファイルの構文チェックを行う
- `systemctl reload nginx`
　　サービスを停止せずに設定変更を反映する


### 3. Set Up MySQL
#### 3.1 Install MySQL and Login
```bash
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo systemctl enable mysql
sudo systemctl status mysql
sudo mysql_secure_installation
sudo mysql
```
※ apt install および systemctl (start/enable/status) の操作はNginxと同様（mysqlに読み替え）
- `mysql_secure_installation`
  　初期セキュリティ設定：不要なユーザーやテストデータベースを削除する
- `mysql`
  　MySQLサーバーに接続する

▶︎ インストール直後に自動で作成される匿名ユーザーは、ユーザー名なしでログインできるため不正アクセスの可能性がある  
▶︎ テストデータベースは権限が緩く、他のデータベース情報を取得されるリスクがある

#### 3.2 Set Up Database
```sql
CREATE DATABASE portfolio_db;
USE portfolio_db;
```
- `CREATE DATABASE <データベース名>`
  　新しいデータベースを作成する
- `USE <データベース名>`
  　操作対象とするデータベースを選択する

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  email VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
- `CREATE TABLE users`
  　ユーザー情報を管理するテーブルを作成する 
  - `id`: ユーザーを識別するID（自動で連番を付与）
  - `name`: ユーザー名（最大50文字）
  - `email`: メールアドレス（最大255文字）
  - `created_at`: 作成日時（自動でレコード作成時の時刻を設定）

```sql
INSERT INTO users (name, email)
VALUES ('Mio Osakada', 'mioosakada@example.com');
```
- `INSERT INTO users (name, email)`
  　usersテーブルのname列とemail列にデータを追加する
- `VALUES`
  　指定したカラムの順番に対応する値を設定する

```sql
SELECT * FROM users;
```
▶︎ usersテーブルの全カラムのデータを取得し、データが正しく登録されているかを確認する

```sql
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(100),
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
- `CREATE TABLE posts`
  　ユーザーの投稿を管理するテーブルを作成する
  - `id`: 投稿を識別するID（usersと同様）
  - `user_id`: 投稿を作成したユーザーのID（usersテーブルのidと対応）
  - `title`: 投稿のタイトル（最大100文字）
  - `content`: 投稿の本文（長文のテキストを保存可能）
  - `created_at`: 作成日時（usersと同様）

```sql
INSERT INTO posts (user_id, title, content)
VALUES (1, 'First Post', 'This is my first post');
```
▶︎ postsテーブルにデータを追加する（INSERT および VALUES の構文はusersと同様）
  
```sql
SELECT * FROM posts;
```
▶︎ postsテーブルのデータを確認する（usersと同様）

```sql
ALTER TABLE posts
ADD CONSTRAINT fk_posts_user_id
FOREIGN KEY (user_id)
REFERENCES users(id)
ON DELETE CASCADE;
```
- `ALTER TABLE posts`
  　既存のpostsテーブルの構造を変更する
- `ADD CONSTRAINT <制約名>`
  　任意の名前で制約（ルール）を追加する
- `FOREIGN KEY (user_id)`
  　user_idカラムを外部キーとして設定する  
  　※外部キー：他のテーブルと関連付けるためのカラム
- `REFERENCES users(id)`
  　usersテーブルのidを参照する  
  　※user_idにはusers.idに存在する値しか入力できない
- `ON DELETE CASCADE`
  　参照される側（親：users）のデータが削除された場合、そのユーザーに紐づくposts（子）のデータも自動で削除する

▶︎ 存在しないユーザーIDの投稿を作成できないようにする  
▶︎ 親（users）データ削除後に、不整合な子（posts）データが残るのを防ぐ

```sql
ALTER TABLE users
MODIFY name VARCHAR(50) NOT NULL,
MODIFY email VARCHAR(255) NOT NULL UNIQUE,
MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
```
▶︎ usersテーブルの既存カラム（name、email、created_at）の定義を変更し、NOT NULL制約（値なしは禁止）およびUNIQUE制約（重複禁止）を追加する

```sql
ALTER TABLE posts
MODIFY user_id INT NOT NULL,
MODIFY title VARCHAR(100) NOT NULL,
MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
```
▶︎ postsテーブルの既存カラムにNOT NULL制約を追加する（usersと同様）  
▶︎ 不完全なデータ登録および重複データの発生を防止し、データの整合性を保つ  

※ 既存データにNULLや重複がある場合、制約追加時にエラーとなるため事前確認が必要


## 4. Get Data (JOIN)
```sql
SELECT users.name, posts.title
FROM users
JOIN posts ON users.id = posts.user_id;
```
▶︎ ユーザーと投稿のデータを結合し、ユーザー名と投稿タイトルを一覧として取得する


## 5. Manage Database Access
```sql
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'Password123!';
```
▶︎ データベース接続用の専用ユーザー（app_user）とパスワードを作成し、rootではなく最小権限のユーザーで接続できるようにする  
▶︎ 接続元をlocalhostに制限し、外部からの不正アクセスを防止する

```sql
GRANT SELECT, INSERT, UPDATE, DELETE
ON portfolio_db.*
TO 'app_user'@'localhost';
```
▶︎ app_userに対して、portfolio_dbデータベース内の全テーブルに対する基本的な操作権限を付与する  
▶︎ 必要最小限の権限（参照・追加・更新・削除）のみを付与することで、誤操作や不正アクセスによる影響範囲の拡大を防ぐ

```sql
SHOW GRANTS FOR 'app_user'@'localhost';
```
▶︎ GRANTで設定した内容が正しく反映されているか、および不要な権限が付与されていないかを確認する  


## 6. Set Up AWS Infrastructure
### 6.1 Initialize AWS Environment
- AWSアカウント作成
- 多要素認証（MFA）有効化
- リージョン設定
  
▶︎ AWSアカウント作成時に生成されるAWS rootユーザーは全権限を持つため、初期設定や緊急時のみに使用を限定する  
▶︎ MFAにより不正ログインを防止する  
▶︎ 東京リージョン（ap-northeast-1）を選択し、データ通信の遅延を低減する  

### 6.2 Configure IAM Users with Permissions
- IAMユーザー作成
- 権限付与
- MFA有効化

▶︎ 日常操作用として管理用のIAMユーザーを作成し、初期構築のために一時的にAdministratorAccessを付与する  
▶︎ IAMユーザーにもMFAを設定し、セキュリティを強化する  

### 6.3 Set Up VPC and Networking
- VPC作成
- サブネット作成
- 自動パブリックIP有効化
- インターネットゲートウェイ作成
- インターネットゲートウェイをVPCにアタッチ
- ルートテーブル設定
- サブネットとルートテーブル関連付け
