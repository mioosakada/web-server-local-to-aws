# Web Server: From Local to AWS Deployment

## Overview
このプロジェクトは、ローカル環境でWebサーバーおよびデータベースを構築し、その環境をAWS上にデプロイする一連の流れを実装したものです。


## Development Environment
- Host OS: macOS (MacBook)
- Local OS: Ubuntu 24.04.4 LTS (UTM)  
  確認コマンド：
  ```bash
  cat /etc/os-release
  ```
- Web Server: Nginx
- Language: HTML / PHP
- Database: MySQL 8.0.45  
  確認コマンド：
  ```bash
  mysql --version
  ```
- Version Control: Git / GitHub
- Cloud: AWS (IAM, VPC, EC2, RDS)
  - Region: ap-northeast-1 (Tokyo)


## Steps
### 0. Check Current User
```bash
whoami
```
▶︎ 実行中のユーザーを確認することで権限や実行環境を把握し、適切なコマンド（sudoの必要性）などを判断する


### 1. Update System
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


### 2. Set Up Local Web Server
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


### 3. Set Up Local MySQL
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
VALUES (1, 'First Post', 'This is my first post.');
```
▶︎ postsテーブルにデータを追加する（INSERT および VALUES の構文はusersと同様）
  
```sql
SELECT * FROM posts;
```
▶︎ postsテーブルのデータを確認する（usersと同様）  

```sql
ALTER TABLE users
MODIFY name VARCHAR(50) NOT NULL,
MODIFY email VARCHAR(255) NOT NULL UNIQUE,
MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
```
- `ALTER TABLE users`  
  既存のusersテーブルの構造を変更する  

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

```sql
ALTER TABLE posts
ADD CONSTRAINT fk_posts_user_id
FOREIGN KEY (user_id)
REFERENCES users(id)
ON DELETE CASCADE;
```
- `ADD CONSTRAINT <制約名>`  
  任意の名前で制約（ルール）を追加する

- `FOREIGN KEY (user_id)`  
  user_idカラムを外部キーとして設定する  
  ※ 外部キー：他のテーブルと関連付けるためのカラム

- `REFERENCES users(id)`  
  usersテーブルのidを参照する    
  ※ user_idにはusers.idに存在する値しか入力できない

- `ON DELETE CASCADE`  
  参照される側（親：users）のデータが削除された場合、そのユーザーに紐づくposts（子）のデータも自動で削除する

▶︎ 存在しないユーザーIDの投稿を作成できないようにする  
▶︎ 親（users）データ削除後に、不整合な子（posts）データが残るのを防ぐ  


#### 3.3 Manage Database Access
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


### 4. Set Up and Run Simple PHP Application
- アプリケーション作成  
  /var/www/html 配下に index.php を作成し、投稿機能を持つ簡易的なPHPアプリケーションを実装する  
  ※ 実装コードの詳細は、本リポジトリ内の index.php を参照

- PHPのインストールと起動設定
  ```bash
  sudo apt install php-fpm php-mysql -y
  sudo systemctl start php8.3-fpm
  sudo systemctl enable php8.3-fpm
  sudo systemctl status php8.3-fpm
  ```
  ※ `php -v`でバージョンを確認し、それに対応するphp-fpmサービスを起動する

- NginxにPHP設定  
  Nginxの設定ファイルを開き、以下のように変更する  
  ```bash
  sudo vi /etc/nginx/sites-available/default
  ```
  変更前：
  ```nginx
  index index.html index.htm;
  ```
  変更後：
  ```nginx
  index index.php index.html index.htm;
  ```
  ▶︎ index.php を優先的に読み込むように設定する  

  変更前：
  ```nginx
  #location ~ \.php$ {
  #        include snippets/fastcgi-php.conf;
  #        # With php-fpm (or other unix sockets):
  #        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
  #        # With php-cgi (or other tcp sockets):
  #        fastcgi_pass 127.0.0.1:9000;
  #}
  ```
  変更後：
  ```nginx
  location ~ \.php$ {
          include snippets/fastcgi-php.conf;
          # With php-fpm (or other unix sockets):
          fastcgi_pass unix:/run/php/php8.3-fpm.sock;
  #       # With php-cgi (or other tcp sockets):
  #       fastcgi_pass 127.0.0.1:9000;
  }
  ```
  ▶︎ PHPファイルを処理できるようにするため、location設定を有効化する（コメントアウト解除）  
  ▶︎ 使用しているPHPバージョンに合わせてphp-fpmのソケットパスを変更する  

- Nginx設定の確認と反映
  ```bash
  sudo nginx -t
  sudo systemctl reload nginx
  ```
  - `nginx -t`  
    設定ファイルの構文チェックを行う

  - `systemctl reload nginx`  
    サービスを停止せずに設定変更を反映する  

  ▶︎ 設定ミスによる起動エラーを防ぎ、安全に設定変更を反映する  

- 動作確認  
  ブラウザで http://IPアドレス にアクセスし、作成したPHPアプリケーションが表示されることを確認  


### 5. Set Up AWS Foundation
#### 5.1 Initialize AWS Environment
- AWSアカウント作成
- 多要素認証（MFA）有効化
- リージョン設定
  
▶︎ AWSアカウント作成時に生成されるAWS rootユーザーは全権限を持つため、初期設定や緊急時のみに使用を限定する  
▶︎ MFAにより不正ログインを防止する  
▶︎ 東京リージョン（ap-northeast-1）を選択し、データ通信の遅延を低減する  


#### 5.2 Configure IAM Users with Permissions
- IAMユーザー作成
- 権限付与
- MFA有効化

▶︎ 日常操作用として管理用のIAMユーザーを作成し、初期構築のために一時的にAdministratorAccessを付与する  
▶︎ IAMユーザーにもMFAを設定し、セキュリティを強化する  


### 6. Set Up VPC and Networking
- VPC作成  
  AWS上に独立したネットワーク環境を構築する  
  ※ CIDRは10.0.0.0/16とし、サブネット分割や将来的な拡張に対応できる設計とする 

- サブネット作成  
  VPC内にサーバー（EC2）を配置するための領域を作成する  
  ※ サブネットは特定のアベイラビリティゾーンに配置し、障害の影響範囲を分離できる構成とする

- 自動パブリックIP有効化  
  サーバーにパブリックIPを自動で割り当て、インターネットと通信可能な状態にする

- インターネットゲートウェイ作成  
  VPCとインターネットを接続するための出入口を作成する

- インターネットゲートウェイをVPCにアタッチ  
  VPCにインターネット接続を有効化する

- ルートテーブル設定  
  通信の経路を定義し、インターネットへの通信をインターネットゲートウェイに送るよう設定する  
  ※ 送信先は0.0.0.0/0とし、VPC外への全ての通信をインターネットゲートウェイ経由で行う設定とする

- サブネットとルートテーブル関連付け  
  サブネットに適用するルートテーブルを設定する  

▶︎ VPCとネットワーク設定により、インターネットと通信可能なサーバー環境を構築する


### 7. Set Up Amazon EC2 
#### 7.1 Launch EC2 Instance
- EC2インスタンス作成  
  AWS上に仮想サーバー（EC2）を起動する

- Amazon マシンイメージ（AMI）選択  
  サーバーのOSとしてUbuntuを選択する  
  ※ ローカル環境と本番環境の差異を減らすため、同一のUbuntu 24.04 LTSを採用

- インスタンスタイプ選択  
  t3.micro（無料枠対象）を選択し、学習用の最小構成とする

- キーペア作成  
  SSH接続時に必要な公開鍵・秘密鍵のペアを作成し、安全にサーバーへ接続できるようにする  
  ※ キーペアタイプは互換性の高いRSAを選択し、MacからSSH接続するため.pem形式を使用  

- ネットワーク設定（VPC・サブネット選択）  
  セクション6で作成したVPCおよびサブネットにEC2を配置する

- セキュリティグループ作成  
  セキュリティグループルールでSSH（22番ポート）とHTTP（80番ポート）のみを許可する  
  ※ SSHは自分のIPのみに制限し、不正アクセスを防ぐ  
  ※ HTTPは任意の場所（0.0.0.0/0）とし、インターネット上の全てのユーザーからアクセス可能にする  

- EC2インスタンス起動  
  設定内容をもとにインスタンスを作成し、サーバーを起動する  


#### 7.2 Connect and Configure EC2 Instance
- 秘密鍵（.pem）の権限変更
  ```bash
  chmod 400 web-server-key.pem
  ```
  - `chmod 400 <ファイル名>`  
    ファイルの権限を「所有者のみ読み取り可」に設定する（セキュリティ対策のため）

- SSH接続確認
  ```bash
  ssh -i web-server-key.pem ubuntu@<EC2のパブリックIPアドレス>
  ```
  - `ssh -i <秘密鍵のファイル名> <ユーザー名>@<パブリックIP>`  
    指定した秘密鍵を使用してリモートサーバーにログインする  

  ※ 初回接続時のみホスト確認メッセージが表示されるため「yes」を入力する  
  
  ▶︎ 接続に成功すると `ubuntu@ip-xxx-xxx-xxx-xxx:~$` のようなプロンプトが表示される  
  
- Webサーバー構築  
  EC2上にWebサーバー（Nginx）をインストールし、外部（ブラウザ）からアクセス可能な状態にする  
  ※ ローカル環境（セクション1・2）と同様の手順をEC2上で実行する  

- ブラウザからアクセス確認  
  パブリックIPアドレスにブラウザでアクセスし、Webページが表示されることを確認する  


### 8. Set Up Amazon RDS
▶︎ 開発・検証用途を前提とし、可用性よりもコスト効率を優先した最小構成とする  

#### 8.1 Create RDS Instance
- エンジンタイプ：MySQL
- データベース作成方法：フル設定
- テンプレート：開発/テスト
- デプロイオプション：シングルAZ  


#### 8.2 Configure RDS Instance Settings
- エンジンバージョン：MySQL 8.0.45  
  ▶︎ ローカル環境と同一バージョンを選択し、SQLの動作差異を防ぐ  

- 認証情報管理：セルフマネージド
- データベース認証オプション：パスワード認証
- インスタンスタイプ：db.t3.micro（バースト可能クラス）
- ストレージタイプ：汎用SSD（gp3）
- ストレージ割り当て：20 GiB  


#### 8.3 Configure Network Settings
- VPC：EC2と同一VPC  
  ▶︎ EC2と同一ネットワーク内に配置し、プライベート通信を可能にする  

- DBサブネットグループ：自動セットアップ
- パブリックアクセス：なし  
  ▶︎ インターネットからの直接アクセスを防ぎ、セキュリティを確保する  

- VPCセキュリティグループ（ファイアウォール）：新規作成  


#### 8.4 Set Up EC2 Connection
- コンピューティングリソース：EC2コンピューティングリソースに接続
- EC2インスタンス：セクション7.1で作成したEC2インスタンスを選択  

▶︎ EC2からRDSへ接続できるよう、対象のEC2インスタンスを関連付ける  


#### 8.5 Verify RDS Connection
- EC2にSSH接続
- RDSに接続



- データベース作成
- PHP接続設定
- 動作確認

## Summary
本プロジェクトを通して、Linuxサーバーの基本操作、Webサーバー構築、データベース設計、およびAWSを用いたインフラ構築の基礎を学習した。
