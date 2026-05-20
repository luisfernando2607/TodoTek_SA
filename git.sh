#!/bin/bash
# =============================================================
# git_commits_todotek.sh  –  Todotek Backend
# =============================================================

# SIN set -e para que no salga silenciosamente en errores menores
set -uo pipefail

# ── Colores ──────────────────────────────────────────────────
RED='\033[0;31m';  GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m';  MAGENTA='\033[0;35m'
BOLD='\033[1m';    DIM='\033[2m';      RESET='\033[0m'

COMMITS_OK=0; COMMITS_SKIP=0; COMMITS_FAIL=0
TOTAL=8
START_TIME=$SECONDS

# ── Helpers ──────────────────────────────────────────────────
separator() { echo -e "${BLUE}────────────────────────────────────────────${RESET}"; }
log_ok()    { echo -e "  ${GREEN}${BOLD}✔  $*${RESET}"; }
log_warn()  { echo -e "  ${YELLOW}⚠  $*${RESET}"; }
log_error() { echo -e "  ${RED}${BOLD}✖  $*${RESET}"; }
log_info()  { echo -e "  ${CYAN}➜  $*${RESET}"; }

# Cuenta regresiva — todo en el hilo principal
countdown() {
    local secs=$1 label="${2:-Siguiente commit}"
    local total=$secs b bar done_n
    for ((i=secs; i>0; i--)); do
        done_n=$(( (total - i) * 20 / total ))
        bar=""
        for ((b=0; b<20; b++)); do
            [[ $b -lt $done_n ]] && bar+="█" || bar+="░"
        done
        printf "\r  ${DIM}⏳ %s en %2ds  [${CYAN}%s${DIM}]${RESET}" "$label" "$i" "$bar"
        sleep 1
    done
    printf "\r\033[K"
}

# Barra de progreso global
progress_bar() {
    local cur=$1 total=$2
    local pct=$(( cur * 100 / total ))
    local filled=$(( cur * 30 / total )) bar="" b
    for ((b=0; b<30; b++)); do
        [[ $b -lt $filled ]] && bar+="█" || bar+="░"
    done
    echo -e "  ${BOLD}Progreso${RESET}  [${GREEN}${bar}${RESET}] ${BOLD}${pct}%${RESET}  (${cur}/${total})"
}

# Stage solo paths que existen
stage_paths() {
    local found=0
    for p in "$@"; do
        if [ -e "$p" ]; then
            git add "$p" 2>/dev/null && echo -e "    ${GREEN}+${RESET} $p" && ((found++)) || log_warn "git add falló en: $p"
        else
            log_warn "No existe, omitido → $p"
        fi
    done
    return 0
}

# Commit si hay algo staged
do_commit() {
    local msg="$1" num="$2"
    if git diff --cached --quiet 2>/dev/null; then
        log_warn "Staging vacío — commit $num omitido (ningún archivo encontrado)."
        ((COMMITS_SKIP++))
        return 0
    fi
    echo -e "\n  ${DIM}Archivos en este commit:${RESET}"
    git diff --cached --name-only | sed 's/^/    📄 /'
    echo ""
    if git commit -m "$msg" --quiet 2>/dev/null; then
        local hash; hash=$(git rev-parse --short HEAD 2>/dev/null || echo "???")
        log_ok "Commit $num completado  ${DIM}[${hash}]${RESET}  \"$msg\""
        ((COMMITS_OK++))
    else
        log_error "Falló el commit $num"
        ((COMMITS_FAIL++))
    fi
    return 0
}

# Un commit completo
run_commit() {
    local num="$1" icon="$2" title="$3" msg="$4" is_last="$5"
    shift 5
    separator
    echo -e "\n${BOLD}[${num}/${TOTAL}] ${icon}  ${title}${RESET}\n"
    stage_paths "$@"
    do_commit "$msg" "$num"
    echo ""
    progress_bar "$num" "$TOTAL"
    echo ""
    if [[ "$is_last" == "no" ]]; then
        local next=$(( num + 1 ))
        countdown 5 "Commit ${next}"
    fi
    echo ""
}

# ── Validaciones ─────────────────────────────────────────────
clear
separator
echo -e "${BOLD}${MAGENTA}  🚀  Todotek Backend – Commits organizados${RESET}"
separator

if ! git rev-parse --is-inside-work-tree &>/dev/null; then
    log_error "No estás dentro de un repositorio git."; exit 1
fi

BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "desconocida")
echo -e "  ${DIM}Rama:${RESET}       ${BOLD}${BRANCH}${RESET}"
echo -e "  ${DIM}Directorio:${RESET} $(pwd)"
separator

# ── COMMITS (ajustados a tu estructura real) ──────────────────

# 1 – Swagger
run_commit 1 "📘" "Swagger / OpenAPI" \
    "docs: integrate Swagger OpenAPI documentation" "no" \
    backend/app/Http/Controllers/SwaggerController.php \
    backend/config/l5-swagger.php \
    backend/storage/api-docs/api-docs.json

# 2 – Sanctum Auth
run_commit 2 "🔐" "Sanctum Authentication" \
    "feat: implement Sanctum authentication API" "no" \
    backend/app/Http/Controllers/Api/AuthController.php \
    backend/app/Models/User.php \
    backend/routes/api.php \
    backend/bootstrap/app.php \
    backend/config/auth.php \
    backend/config/sanctum.php \
    "backend/database/migrations/2026_05_20_140139_create_personal_access_tokens_table.php"

# 3 – Controllers API
run_commit 3 "🎮" "API Controllers" \
    "feat: add resource API controllers" "no" \
    backend/app/Http/Controllers/Api/CategoryController.php \
    backend/app/Http/Controllers/Api/ClientController.php \
    backend/app/Http/Controllers/Api/InvoiceController.php \
    backend/app/Http/Controllers/Api/ProductController.php \
    backend/app/Http/Controllers/Api/ProductImageController.php \
    backend/app/Http/Controllers/Api/StockController.php

# 4 – Models
run_commit 4 "📦" "Business Models" \
    "feat: add business entity models" "no" \
    backend/app/Models/Category.php \
    backend/app/Models/Client.php \
    backend/app/Models/Invoice.php \
    backend/app/Models/InvoiceItem.php \
    backend/app/Models/Product.php \
    backend/app/Models/ProductImage.php \
    backend/app/Models/StockMovement.php

# 5 – Services
run_commit 5 "⚙️" "Business Services" \
    "feat: add business service layer" "no" \
    backend/app/Services/InvoiceService.php \
    backend/app/Services/ProductService.php \
    backend/app/Services/StockService.php

# 6 – Requests
run_commit 6 "🧾" "Form Requests" \
    "feat: add request validations" "no" \
    backend/app/Http/Requests/StoreProductRequest.php

# 7 – Seeders
run_commit 7 "🌱" "Database Seeders" \
    "feat: add initial database seeders" "no" \
    backend/database/seeders/CategorySeeder.php \
    backend/database/seeders/ClientSeeder.php \
    backend/database/seeders/DatabaseSeeder.php \
    backend/database/seeders/ProductSeeder.php \
    backend/database/seeders/UserSeeder.php

# 8 – Old migrations + Composer
run_commit 8 "🗂" "Migrations backup + Composer" \
    "chore: backup legacy migrations and update dependencies" "yes" \
    backend/database/old_migrations/ \
    backend/composer.json \
    backend/composer.lock \
    backend/config/cache.php

# ── Resumen ──────────────────────────────────────────────────
separator
ELAPSED=$(( SECONDS - START_TIME ))
echo -e "${BOLD}  📊  Resumen final${RESET}"
echo -e "  ${GREEN}✔  Realizados : ${COMMITS_OK}${RESET}"
echo -e "  ${YELLOW}⏭  Omitidos   : ${COMMITS_SKIP}${RESET}"
echo -e "  ${RED}✖  Fallidos   : ${COMMITS_FAIL}${RESET}"
echo -e "  ${DIM}⏱  Tiempo     : ${ELAPSED}s${RESET}"
separator

if (( COMMITS_FAIL > 0 )); then
    log_error "Hubo errores. Revisa los mensajes arriba."
    exit 1
fi

echo -e "\n${GREEN}${BOLD}  🎉  ¡Commits completados exitosamente!${RESET}\n"

# ── Push automático ──────────────────────────────────────────
separator
echo -e "${BOLD}  📡  Subiendo a GitHub...${RESET}\n"

REMOTE=$(git remote 2>/dev/null | head -1)
BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)

if [[ -z "$REMOTE" ]]; then
    log_error "No hay remoto configurado. Agrega uno con: git remote add origin <url>"
    exit 1
fi

echo -e "  ${DIM}Remoto:${RESET} ${BOLD}${REMOTE}${RESET}  →  $(git remote get-url "$REMOTE" 2>/dev/null)"
echo -e "  ${DIM}Rama:${RESET}   ${BOLD}${BRANCH}${RESET}\n"

if git push "$REMOTE" "$BRANCH"; then
    separator
    log_ok "¡Push completado! Todos los commits están en GitHub. 🚀"
else
    separator
    log_error "El push falló. Verifica tu conexión o permisos."
    exit 1
fi
separator
