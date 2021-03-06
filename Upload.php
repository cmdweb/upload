<?php
/**
 * User: Gabriel Malaquias
 * Date: 26/06/2015
 * Time: 22:58
 */

namespace Alcatraz\Upload;

class Upload {

    /**
     * @param $arquivo
     * @param $nome
     * @param int $mb
     * @return mixed
     */
    public static function upload($file, $name, $folder, $mb = 15, $extensoes = array(),$inverseExtensao = false)
    {
        // Pasta onde o arquivo vai ser salvo
        $_UP['pasta'] = $folder;
        // Tamanho m�ximo do arquivo (em Bytes)
        $_UP['tamanho'] = 1024 * 1024 * $mb; // 15Mb
        // Array com as extens�es permitidas
        //$_UP['extensoes'] = array('jpg', 'png', 'gif','docx', 'doc', 'pdf', 'ppt', 'pptx');
        // Renomeia o arquivo? (Se true, o arquivo ser� salvo como .jpg e um nome �nico)
        $_UP['renomeia'] = true;

        // Caso script chegue a esse ponto, n�o houve erro com o upload e o PHP pode continuar
        // Faz a verifica��o da extens�o do arquivo
        $ex = explode('.', $file['name']);
        $extensao = strtolower($ex[count($ex) - 1]);
        if (count($extensoes) > 0) {
            if ($inverseExtensao) {
                if (!(array_search($extensao, $extensoes) === false)){
                    throw new \Exception("Não é posivel fazer uploads na(s) seguinte(s) extensão(ões): " . implode(",", $extensoes) . ".");
                }
            } else {
                if (array_search($extensao, $extensoes) == false) {
                    throw new \Exception("Por favor, envie arquivos com a(s) seguinte(s) extensão(ões): " . implode(",", $extensoes) . ".");
                }
            }
        }

        // Faz a verifica��o do tamanho do arquivo
        if ($_UP['tamanho'] < $file['size']) {
            return null;
        }

        // O arquivo passou em todas as verifica��es, hora de tentar mov�-lo para a pasta
        else {
            // Primeiro verifica se deve trocar o nome do arquivo
            if ($_UP['renomeia'] == false) {
                // Cria um nome baseado no UNIX TIMESTAMP atual e com extens�o .jpg
                $nome_final = time().'.'.$extensao;
            } else {
                // Mant�m o nome original do arquivo
                $nome_final = $name.'.'.$extensao;
                if(file_exists($_UP['pasta'].$nome_final)){
                    $nome_final = $name.date('is').'.'.$extensao;
                }
            }

            // Depois verifica se � poss�vel mover o arquivo para a pasta escolhida
            if (move_uploaded_file($file['tmp_name'], $_UP['pasta'] . $nome_final))
                return $nome_final;
        }
    }


    public static function uploadImg($tmp, $name, $nome_imagem, $larguraP, $pasta){
        $ext = strtolower($name);
        $aux = explode('.',$ext);
        $ext  = end($aux);

        if(!in_array($ext,array("jpg","png","gif")))
            throw new \Exception("Imagem inválida, a imagem deve ser JPG, GIF ou PNG.");

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
            imagejpeg($nova, $pasta."$nome_imagem");
            imagedestroy($nova);
        }

        return($nome_imagem);
    }
}