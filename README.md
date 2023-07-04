1. Clone a repo
```bash
git clone git@bitbucket.com:innovation-Latam/platform.git
```

2. Copie `.env.example` to `.env`
```bash
cp .env.example .env
```

3. Setup do container
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php74-composer:latest \
    composer install --ignore-platform-reqs
```

4. Configurar alias para o Laravel Sail
```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc && source ~/.bashrc
```

5. Instalação da aplicação
```bash
sail up -d && sail bash install.sh
```

## Execução em ambiente local
1. Ative o uma instância de container Docker
```bash
sail up -d
```

2. Gerar banco local
```bash
sail artisan migrate:fresh --seed
```

3. Gerar assets
```bash
sail npm run watch
```
