# Build arguments (required)
ARG APP_FRONTEND_BASE_URL
ARG NEXT_SERVER_SIDE_BACKEND_URL


# Install project dependencies
FROM node:lts-alpine AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app
COPY package.json yarn.lock ./
RUN yarn install --frozen-lockfile

# Build for production
FROM node:lts-alpine AS builder

ENV NODE_ENV=production
ENV APP_FRONTEND_BASE_URL=${APP_FRONTEND_BASE_URL}
ENV NEXT_SERVER_SIDE_BACKEND_URL=${NEXT_SERVER_SIDE_BACKEND_URL}

WORKDIR /app
COPY . .
COPY --from=deps /app/node_modules ./node_modules
RUN yarn build

# Run server
FROM node:lts-alpine AS runner
WORKDIR /app

ENV APP_FRONTEND_BASE_URL=${APP_FRONTEND_BASE_URL}
ENV NEXT_SERVER_SIDE_BACKEND_URL=${NEXT_SERVER_SIDE_BACKEND_URL}
ENV NODE_ENV production

RUN addgroup -g 1001 -S nodejs
RUN adduser -S nextjs -u 1001
COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next ./.next
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json

USER nextjs
EXPOSE 3000
ENV NEXT_TELEMETRY_DISABLED 1
CMD ["yarn", "start"]