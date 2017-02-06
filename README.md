# schezzo

# 環境構築手順

ローカル環境の構築手順です

## 前準備

Vagrantに必要なツールをインストールしてください

- Virtual Box https://www.virtualbox.org/
- Vagrant http://www.vagrantup.com/

## セットアップ

必要なプラグインのインストール

```sh
vagrant plugin install vagrant-omnibus
vagrant plugin install vagrant-vbguest
```

hostsの編集

```
172.16.22.20    schezzo.vagrant.net
172.16.22.20    admin.schezzo.vagrant.net
```

vagrant の開始

```sh
vagrant up
```

ansible のレシピは自動で実行されます

#### 2回目以降

次からは up するだけで vm 環境が立ち上がります

```sh
vagrant up
```

レシピの変更があった際には provision で立ち上げてください

すでに vagrant が立ち上がっている場合

```sh
vagrant provision
```

まだ vagrant が立ち上がっていない場合

```sh
vagrant up -provision
```

## migration について

DBの変更はmigrationで管理しています。

変更があった場合は

```
vagrant ssh
```

```
cd /var/www/schezzo/src
php yii migrate/up
```

を実行してください
