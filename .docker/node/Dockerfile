FROM node:22.11-alpine

ENV HOST 0.0.0.0

ARG user
ARG uid

WORKDIR /var/www/html

#COPY ./backend/package.json ./backend/package-lock.json ./

#RUN npm install --frozen-lockfile

COPY ./backend .

RUN chown -R $user:$user /var/www/html && chmod -R 755 /var/www/html

USER $user

EXPOSE 5173

#CMD ["npm", "run", "dev"]
