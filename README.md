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
