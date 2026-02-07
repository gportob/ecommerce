#!/bin/bash

echo "=== Testando API de Carrinho ==="

# Testar login primeiro (criar sessão)
echo "1. Simulando login..."
curl -s -c /tmp/cookies.txt "http://localhost/login" > /dev/null

# Testar adição ao carrinho sem estar logado
echo "2. Testando adicionar ao carrinho sem login..."
curl -s -b /tmp/cookies.txt -X POST http://localhost/api/add-to-cart.php \
  -d "product_id=1&quantity=1&size=M" \
  -H "Content-Type: application/x-www-form-urlencoded"

echo ""
echo "3. Testando obter contagem do carrinho..."
curl -s -b /tmp/cookies.txt http://localhost/api/get-cart-count.php

echo ""
echo "=== Testes Completados ==="
