  // Depurador recorrente. Comente, porém não apage ---------------------------------------------------------------------------------------
        // Alpha Engine: Debugger Ultra-Leve (Crash-Proof & Memory Safe)
        
        $logFile = DIR_LOGS . 'queries.php';
        if (!file_exists($logFile)) {
            file_put_contents($logFile, "<?php die('Acesso Restrito'); ?>\n\n");
        }
        
        $safeParams = [];
        $runnableSql = $builder->getSQL();
        
        foreach ($builder->getParams() ?? [] as $param) {
            if (is_string($param) && strlen($param) > 500) {
                $safeParam = substr($param, 0, 500) . '... [TRUNCATED, SIZE: ' . strlen($param) . ' bytes]';
            } else {
                $safeParam = $param;
            }
            $safeParams[] = $safeParam;
            
            // Formata o valor para a query executável
            if ($safeParam === null) {
                $value = 'NULL';
            } elseif (is_bool($safeParam)) {
                $value = $safeParam ? '1' : '0';
            } elseif (is_numeric($safeParam) && !is_string($safeParam)) {
                $value = (string)$safeParam;
            } else {
                $value = "'" . addslashes((string)$safeParam) . "'";
            }
            
            // Substitui o primeiro '?' encontrado (Seguro contra Memory Leaks e Backreferences)
            $pos = strpos($runnableSql, '?');
            if ($pos !== false) {
                $runnableSql = substr_replace($runnableSql, $value, $pos, 1);
            }
        }
        
        // Usamos error_log (unbuffered) para forçar a gravação instantânea no disco, mesmo se a linha abaixo der OOM
        error_log("[" . date('Y-m-d H:i:s') . "] " . $runnableSql . "\n", 3, $logFile);
        // ------------------------------------------------------------------------------------------------------------------------------------------