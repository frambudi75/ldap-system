FROM python:3.11-slim

WORKDIR /app

# Install build dependencies for bcrypt and CFFI
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    libffi-dev \
    && rm -rf /var/lib/apt/lists/*

# Install python dependencies
COPY daemon/requirements.txt /app/requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Copy daemon source code
COPY daemon /app

EXPOSE 389

CMD ["python", "-u", "server.py"]
