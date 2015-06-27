<?php
/**
 * User: Gabriel Malaquias
 * Date: 26/06/2015
 * Time: 22:58
 */

class Upload {

    /**
     * @param $arquivo
     * @param $nome
     * @param int $mb
     * @return mixed
     */
    function upload($file, $name, $folder, $mb = 15){
        // Pasta onde o arquivo vai ser salvo
        $_UP['pasta'] =$folder;
        // Tamanho m�ximo do arquivo (em Bytes)
        $_UP['tamanho'] = 1024 * 1024 * $mb; // 15Mb
        // Array com as extens�es permitidas
        //$_UP['extensoes'] = array('jpg', 'png', 'gif','docx', 'doc', 'pdf', 'ppt', 'pptx');
        // Renomeia o arquivo? (Se true, o arquivo ser� salvo como .jpg e um nome �nico)
        $_UP['renomeia'] = true;
        // Array com os tipos de erros de upload do PHP
        $_UP['erros'][0] = 'Não houve erro';
        $_UP['erros'][1] = 'O arquivo no upload é maior do que o limite';
        $_UP['erros'][2] = 'O arquivo ultrapassa o limite de tamanho especifiado no HTML';
        $_UP['erros'][3] = 'O upload do arquivo foi feito parcialmente';
        $_UP['erros'][4] = 'Não foi feito o upload do arquivo';

        // Verifica se houve algum erro com o upload. Se sim, exibe a mensagem do erro
        if ($file['error'] != 0) {
            die("Não foi possível fazer o upload, erro:<br />" . $_UP['erros'][$file['error']]);
            exit; // Para a execu��o do script
        }
        // Caso script chegue a esse ponto, n�o houve erro com o upload e o PHP pode continuar
        // Faz a verifica��o da extens�o do arquivo
        $extensao = strtolower(end(explode('.', $file['name'])));
        /*if (array_search($extensao, $_UP['extensoes']) === false) {
            echo "Por favor, envie arquivos com as seguintes extens�es: jpg, png ou gif";
        }*/
        // Faz a verifica��o do tamanho do arquivo
        if ($_UP['tamanho'] < $file['size']) {
            $erro = "O arquivo enviado é muito grande, envie arquivos de até 15Mb.";
            $return['erro'] = $erro;
        }

        // O arquivo passou em todas as verifica��es, hora de tentar mov�-lo para a pasta
        else {
            // Primeiro verifica se deve trocar o nome do arquivo
            if ($_UP['renomeia'] == false) {
                // Cria um nome baseado no UNIX TIMESTAMP atual e com extens�o .jpg
                $nome_final = time().'.jpg';
            } else {
                // Mant�m o nome original do arquivo
                $nome_final = $name.'.'.$extensao;
                if(file_exists($_UP['pasta'].$nome_final)){
                    $nome_final = $name.date('is').'.'.$extensao;
                }
            }

            // Depois verifica se � poss�vel mover o arquivo para a pasta escolhida
            if (move_uploaded_file($file['tmp_name'], $_UP['pasta'] . $nome_final)) {
                // Upload efetuado com sucesso, exibe uma mensagem e um link para o arquivo
            } else {
                // N�o foi poss�vel fazer o upload, provavelmente a pasta est� incorreta
                $erro = "Não foi possível enviar o arquivo, tente novamente";
                $return['erro'] = $erro;
            }

            $return['nome'] = $nome_final;

            return $return;
        }
    }


    function upload_img($tmp, $name, $nome_imagem, $larguraP, $pasta){
        $ext = strtolower($name);
        $aux =explode('.',$ext);
        $ext  = end($aux);
        $a = 1;
        if($ext =='jpg'){
            $img = imagecreatefromjpeg($tmp);
        }elseif($ext=='gif'){
            $img = imagecreatefromgif($tmp);
        }else{
            $img = imagecreatefrompng($tmp);
            $a = 2;
        }
        $x = imagesx($img);
        $y = imagesy($img);

        $largura = ($x>$larguraP) ? $larguraP : $x;
        $altura = ($largura * $y)/ $x;

        if($altura>$larguraP){
            $altura = $larguraP;
            $largura = ($altura*$x) / $x;
        }

        $nova = imagecreatetruecolor($largura, $altura);
        if($a == 2){
            imagealphablending ($nova, true);
            $transparente = imagecolorallocatealpha ($nova, 0, 0, 0, 127);
            imagefill ($nova, 0, 0, $transparente);
            imagecopyresampled($nova, $img, 0, 0, 0, 0, $largura, $altura, $x, $y);
            imagesavealpha($nova, true);
            imagedestroy($img);
            imagepng($nova, $pasta."/$nome_imagem");
            imagedestroy($nova);
        }else{
            imagecopyresampled($nova, $img, 0, 0, 0, 0, $largura, $altura, $x, $y);
            imagedestroy($img);
            imagejpeg($nova, $pasta."/$nome_imagem");
            imagedestroy($nova);
        }

        return($nome_imagem);

    }

}