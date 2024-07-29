# Use the latest stable version of Nginx
FROM nginx:stable-alpine

# Set working directory
WORKDIR /app

# Copy the public directory
COPY ./public/ /app/public/

# Copy the nginx config file
COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Expose the necessary port
EXPOSE 80

# Command to run Nginx
CMD ["nginx", "-g", "daemon off;"]
