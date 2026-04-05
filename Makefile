# EdgeDeck — local development shortcuts

.PHONY: serve api-smoke

serve:
	php -S 127.0.0.1:8080 -t backend backend/router.php

api-smoke:
	backend/scripts/api-smoke.sh
