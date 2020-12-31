<?php
class Log {
    /**
     * FR : Class Log enregistre les $message pré-fait dans le fichier log.
     * Ces messages seront préfixés de la date et de l'heure.
     * L'Ip du destinataire est suivi de la page consultée ainsi
     * que du message concerné.
     * Tous les mois le fichier sera copié vers un autre dossier et vider.
     *  */ 
    /**
     * EN : Log class record the pre-made message in a log file.
     * These logs would be prefixed by date and time of the message.
     * This file would copy to another folder and then emptied every month.
     */
    
    public function logger($message) {
        date_default_timezone_set('Europe/Paris');
        
        $current_datetime = "[" . date('d/m/Y H:i:s') . "]";
        $url = $_SERVER['REMOTE_ADDR']." connect to ".$_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
        $output = $current_datetime.$url.$message."\n";

        $file = fopen("logs\log.txt", "a+");
        fputs($file, $output);
        fclose($file);
        $this->oldlogs($file);
    }

    /** 
     * Vérification existance fichier log
     * et enregistrement de la derniere date de modification 
     */
    function oldlogs() {
        date_default_timezone_set('Europe/Paris');
        $filename = "logs\log.txt";
        if (!file_exists($filename)){
            fopen('logs\log.txt' ,"a+");
        }
        $date_file = new DateTime();
        $date_file->format('Y/m/d');
        $last_date_modification = date("d/m/Y", fileatime($filename))."\n";
        $date1 = new DateTime();
        $date1->format('Y/m/d');
        $date = date('d/m/Y');


        // Copie le contenu de log dans un autre dossier et nettoie le fichier log
        $origin = new DateTime('2020-12-31 14:24:00');
        $target = new DateTime();
        $interval = $origin->diff($target);
        //echo $interval->format('%R%a days');
        if ($interval->i > 1) { // une minute
            $file_prefix = strtotime(date('m/Y'));
            $filedest = "logs\old_logs\Old_logs.txt";
            fopen($filedest, 'a+');
            copy($filename, $filedest);
            unlink($filename);
            //fopen($filename);
            }
    }
    // Retourn la dernière date de modification
    function lastDateModification($filename) {
        if (file_exists($filename)) {
            $last_date_modification = date("d/m/Y", fileatime($filename))."\n";
        }
        return $last_date_modification;
    }

}