# RootRepair

**RootRepair** ir nelielas Latvijas remontdarbnīcas mācību laboratorija. Uzņēmums dibināts 2015. gadā, kad Juris Kalniņš sāka remontēt datorus, telefonus un spēļu konsoles nelielā darbnīcā. Gadu gaitā pievienojās tehniķu komanda, klientu pieteikumu sistēma un diagnostikas rīki.
Šī tīmekļa lietotne attēlo darbnīcas ikdienu — remonta pieteikumus, klientu profilus, iekšējās piezīmes un darbinieku funkcijas. 

Visi uzņēmuma, darbinieku un klientu dati ir izdomāti.

## STARTS
# Windows
Nepieciešams Docker Desktop ar Linux konteineru režīmu un PowerShell.

```powershell
cd C:\Users\yuuku\Desktop\RootRepair-vuln-web
docker compose up -d --build --wait
```
# Linux

```bash
git clone https://github.com/crrawl/Root-Repair-Service.git
sudo apt update
sudo apt install docker.io
sudo apt install docker-compose
cd Root-Repair-Service
sudo docker compose up --build

sudo docker compose down # izslēgt
```
Pēc palaišanas:

```bash
vim /etc/hosts 
    127.0.0.1   rootrepair.lv 

```
