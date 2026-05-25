#!/bin/bash
# ================================================================
# setup.sh — Verificador de estructura del frontend Todostock
# Ejecutar desde la raíz del proyecto frontend:
#   bash setup.sh
# ================================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ok()   { echo -e "${GREEN}✅ OK${NC}   $1"; }
fail() { echo -e "${RED}❌ FALTA${NC} $1"; ERRORS=$((ERRORS+1)); }
warn() { echo -e "${YELLOW}⚠️  ${NC}$1"; }

ERRORS=0

echo ""
echo "================================================"
echo "  Todostock Frontend — Verificación de archivos"
echo "================================================"
echo ""

# ── Archivos raíz ──────────────────────────────────────────────
echo "--- Configuración raíz ---"
[ -f "angular.json" ]       && ok "angular.json"       || fail "angular.json"
[ -f "tsconfig.json" ]      && ok "tsconfig.json"      || fail "tsconfig.json"
[ -f "tsconfig.app.json" ]  && ok "tsconfig.app.json"  || fail "tsconfig.app.json"
[ -f "package.json" ]       && ok "package.json"       || fail "package.json"
[ -f "proxy.conf.json" ]    && ok "proxy.conf.json"    || fail "proxy.conf.json"

# ── src/ ───────────────────────────────────────────────────────
echo ""
echo "--- src/ ---"
[ -f "src/main.ts" ]        && ok "src/main.ts"        || fail "src/main.ts"
[ -f "src/index.html" ]     && ok "src/index.html"     || fail "src/index.html"
[ -f "src/styles.scss" ]    && ok "src/styles.scss"    || fail "src/styles.scss"

# ── app/ ───────────────────────────────────────────────────────
echo ""
echo "--- src/app/ ---"
[ -f "src/app/app.component.ts" ] && ok "app.component.ts" || fail "app.component.ts"
[ -f "src/app/app.config.ts" ]    && ok "app.config.ts"    || fail "app.config.ts"
[ -f "src/app/app.routes.ts" ]    && ok "app.routes.ts"    || fail "app.routes.ts"

# ── core/ ──────────────────────────────────────────────────────
echo ""
echo "--- core/ ---"
[ -f "src/app/core/guards/auth.guard.ts" ]             && ok "guards/auth.guard.ts"             || fail "guards/auth.guard.ts"
[ -f "src/app/core/interceptors/auth.interceptor.ts" ] && ok "interceptors/auth.interceptor.ts" || fail "interceptors/auth.interceptor.ts"
[ -f "src/app/core/models/models.ts" ]                 && ok "models/models.ts"                 || fail "models/models.ts"
[ -f "src/app/core/services/auth.service.ts" ]         && ok "services/auth.service.ts"         || fail "services/auth.service.ts"
[ -f "src/app/core/services/product.service.ts" ]      && ok "services/product.service.ts"      || fail "services/product.service.ts"
[ -f "src/app/core/services/api.services.ts" ]         && ok "services/api.services.ts"         || fail "services/api.services.ts"
[ -f "src/app/core/services/notification.service.ts" ] && ok "services/notification.service.ts" || fail "services/notification.service.ts"

# ── features/ ─────────────────────────────────────────────────
echo ""
echo "--- features/ ---"
[ -f "src/app/features/auth/login.component.ts" ]                         && ok "auth/login.component.ts"                         || fail "auth/login.component.ts"
[ -f "src/app/features/auth/login.component.html" ]                       && ok "auth/login.component.html"                       || fail "auth/login.component.html"
[ -f "src/app/features/products/product-list.component.ts" ]              && ok "products/product-list.component.ts"              || fail "products/product-list.component.ts"
[ -f "src/app/features/products/product-list.component.html" ]            && ok "products/product-list.component.html"            || fail "products/product-list.component.html"
[ -f "src/app/features/products/product-form-modal.component.ts" ]        && ok "products/product-form-modal.component.ts"        || fail "products/product-form-modal.component.ts"
[ -f "src/app/features/products/product-form-modal.component.html" ]      && ok "products/product-form-modal.component.html"      || fail "products/product-form-modal.component.html"
[ -f "src/app/features/products/stock-modal.component.ts" ]               && ok "products/stock-modal.component.ts"               || fail "products/stock-modal.component.ts"
[ -f "src/app/features/products/stock-modal.component.html" ]             && ok "products/stock-modal.component.html"             || fail "products/stock-modal.component.html"
[ -f "src/app/features/categories/category-list.component.ts" ]           && ok "categories/category-list.component.ts"           || fail "categories/category-list.component.ts"
[ -f "src/app/features/clients/client-list.component.ts" ]                && ok "clients/client-list.component.ts"                || fail "clients/client-list.component.ts"
[ -f "src/app/features/invoices/invoice-list.component.ts" ]              && ok "invoices/invoice-list.component.ts"              || fail "invoices/invoice-list.component.ts"
[ -f "src/app/features/invoices/invoice-list.component.html" ]            && ok "invoices/invoice-list.component.html"            || fail "invoices/invoice-list.component.html"
[ -f "src/app/features/invoices/invoice-create.component.ts" ]            && ok "invoices/invoice-create.component.ts"            || fail "invoices/invoice-create.component.ts"
[ -f "src/app/features/invoices/invoice-create.component.html" ]          && ok "invoices/invoice-create.component.html"          || fail "invoices/invoice-create.component.html"

# ── layout/ ────────────────────────────────────────────────────
echo ""
echo "--- layout/ ---"
[ -f "src/app/layout/main-layout.component.ts" ]          && ok "layout/main-layout.component.ts"     || fail "layout/main-layout.component.ts"
[ -f "src/app/layout/sidebar/sidebar.component.ts" ]      && ok "sidebar/sidebar.component.ts"        || fail "sidebar/sidebar.component.ts"
[ -f "src/app/layout/sidebar/sidebar.component.html" ]    && ok "sidebar/sidebar.component.html"      || fail "sidebar/sidebar.component.html"

# ── node_modules ───────────────────────────────────────────────
echo ""
echo "--- Dependencias ---"
[ -d "node_modules" ] && ok "node_modules instalado" || warn "node_modules NO existe — ejecuta: npm install"

# ── Resultado ──────────────────────────────────────────────────
echo ""
echo "================================================"
if [ $ERRORS -eq 0 ]; then
  echo -e "${GREEN}✅ Todo correcto. Ejecuta: npm start${NC}"
else
  echo -e "${RED}❌ Faltan $ERRORS archivos. Cópialos a las rutas indicadas arriba.${NC}"
  echo ""
  echo "Los archivos están en: outputs/todostock/frontend/"
  echo "Copia la estructura completa con:"
  echo "  cp -r outputs/todostock/frontend/src/* tu-proyecto/src/"
  echo "  cp outputs/todostock/frontend/angular.json tu-proyecto/"
  echo "  cp outputs/todostock/frontend/tsconfig*.json tu-proyecto/"
  echo "  cp outputs/todostock/frontend/proxy.conf.json tu-proyecto/"
fi
echo "================================================"
echo ""