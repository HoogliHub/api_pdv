1. Clone a repo
```bash
git clone git@github.com:HoogliHub/api_pdv.git
```

2. Copie `.env.example` to `.env`
```bash
cp .env.example .env
```

3. Setup do container
```bash
./vendor/bin/sail up
```

4. Configurar alias para o Laravel Sail
```bash
echo alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
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

