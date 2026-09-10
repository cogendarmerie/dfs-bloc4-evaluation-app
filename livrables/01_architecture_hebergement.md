# Architecture

## Analyse

Le projet contient :
- Un framework Laravel nécessitant du PHP
- Un framework Next.js nécessitant Node.js
- Redis
- Une base de donnée relationnelle Mysql et une orientée document MongoDB

L'hébergement de tout les services s'effectuerait sur une seul machine pour respecter la contraintes économiques avec : 

```
VPS
├── Nginx
├── PHP-FPM
├── Laravel
├── Node.js
├── Next.js
├── MySQL
├── MongoDB
├── Redis
└── éventuellement Supervisor
```

Je metterais plusieurs environements en place avec :

- Docker en développement
- Une machine de qualification pour tester l'application avant la mise en prod
- Une machine de production

### Ouverture de ports
Pour permettre l'accès aux services sur le VPS à distance, nous allons devoir ouvrir plusieurs ports :
- 80/443 pour HTTP/HTTPS
- 22 pour le SSH, mais avec une limitation sur les IP autorisée, seul celles enregistrée pourront s'y connecter


### Schema

                         INTERNET
                            │
                     DNS → IP du VPS
                            │
                       HTTPS / TLS
                            │
                         NGINX
                            │
              ┌─────────────┴─────────────┐
              │                           │
              ▼                           ▼
        Laravel 12                    Next.js
       Front + API                dispatch-dashboard
              │
      ┌───────┼────────┐
      │       │        │
      ▼       ▼        ▼
    MySQL   MongoDB   Redis
      │       │        │
      └───────┴────────┘
              │
              ▼
        Sauvegardes


### Evolution possible

Dans le futur on pourrait imaginer une architecture multi VPS pour répondre a des problématiques de charge ou des critères de disponibilité avec :

- Un load-balancer
- Plusieurs VPS/Container pour partager la charge
- Une base de donnée en lecture/ecriture car il n'est pas possible d'avoir plusieurs BDD en ecriture
- D'autres base de donnée en lecture seul synchroniser avec la première en lecture / ecriture

Seul le load balancer exposerait un port, le 443, les autres VPS ne serait accessibles que un interne


                    Internet
                       │
                  Load Balancer
                       │
             ┌─────────┴─────────┐
             │                   │
         Laravel x2           Next.js x2
             │
       ┌─────┼──────┐
       │     │      │
    MySQL  MongoDB Redis
   managé   managé  managé
       │
       ▼
   Backups externes



## Choix du fournisseur

Pour le fournisseur, je choisirais OVHCloud dans un premier lieux, il a l'avantage d'être Européen, avec plusieurs datacenters disponible en France. Et surtout pour débuter il propose des prix intéressant. Avec la possibilité d'augmenter les ressources du VPS si la charge augmente (non infini).

Inclus par défault des protection anti DDos dans certaines offres. Possibilités de mettre en place des backup.

Une architecture type AWS, n'est pas utile et ne respecte pas le budget. Elle n'est pas a ecarté non plus pour de potentiel amélioration par la suite.

### Estimation tarifaire

- OVHCloud ~200€ / an
- Nom de domaine ~15€ /an

On peux donc estimer un cout annuel d'environ 215€ par ans.

