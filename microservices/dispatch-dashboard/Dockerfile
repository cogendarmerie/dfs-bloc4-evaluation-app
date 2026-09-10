FROM node:22-alpine

WORKDIR /app

# Installation des dépendances
COPY package*.json ./

RUN npm install

# Copie du projet
COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev"]