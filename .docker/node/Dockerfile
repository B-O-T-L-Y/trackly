FROM node:24.11-alpine

ENV HOST 0.0.0.0

ARG user
ARG uid

WORKDIR /var/www/html

COPY ./frontend/package.json ./frontend/yarn.lock ./

RUN yarn install --frozen-lockfile

COPY ./frontend .

RUN chown -R $user:$user /var/www/html && chmod -R 755 /var/www/html

RUN chmod u+x ./entrypoint.sh

USER $user

EXPOSE 3000

ENTRYPOINT ["sh", "./entrypoint.sh"]

CMD ["yarn", "dev", "--open"]
