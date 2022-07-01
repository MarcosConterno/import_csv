<?php
/*
  Descrição do Desafio:
    Você precisa realizar uma migração dos dados fictícios que estão na pasta <dados_sistema_legado> para a base da clínica fictícia MedicalChallenge.
    Para isso, você precisa:
      1. Instalar o MariaDB na sua máquina. Dica: Você pode utilizar Docker para isso;
      2. Restaurar o banco da clínica fictícia Medical Challenge: arquivo <medical_challenge_schema>;
      3. Migrar os dados do sistema legado fictício que estão na pasta <dados_sistema_legado>:
        a) Dica: você pode criar uma função para importar os arquivos do formato CSV para uma tabela em um banco 
        temporário no seu MariaDB.
      4. Gerar um dump dos dados já migrados para o banco da clínica fictícia Medical Challenge.
*/

// Importação de Bibliotecas:
include 'conexao.php';
include "./lib.php";



// Informações de Inicio da Migração:
echo "Início da Migração: " . dateNow() . ".\n\n";


//Leitura dos Convenios no Arquivo Agendamento
$convenios = [];//array de mapeamento de convenios
$profissionais = []; //array de mapeamento de profissionais
$procedimentos = []; //array de mapeamento de procedimentos
$pacientes = []; //array de mapeamento de pacientes
if (($handle = fopen("./dados/20210512_agendamentos.csv", "r")) !== FALSE) {
  //para não pegar a primeira linha
  fgets($handle);
  //while para percorrer o arquivo, inserindo a quebra de linhas, aqui limitado a um arquivo com no máximo 1000 linhas
  while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

    //importacao de convenios
    $id_convenio = $data[9];
    $nome_convenio = $data[10];
    $convenio_db_result = mysqli_query($connMedical, "SELECT * FROM convenios WHERE nome = '$nome_convenio'", MYSQLI_USE_RESULT);
    $convenio_db = mysqli_fetch_assoc($convenio_db_result);
    mysqli_free_result($convenio_db_result);

    if ($convenio_db) {
        //mapeia convenio existente no banco
        $convenios[$id_convenio] = $convenio_db['id'];
    } else {
        //cria novo convenio e mapeia o id retornado
        mysqli_query($connMedical, "INSERT INTO convenios (nome, descricao) 
                              VALUES ('$nome_convenio', 'Convenio Cadastrado Importacao')");
        $novo_convenio_id = mysqli_insert_id($connMedical);
        $convenios[$id_convenio] =  $novo_convenio_id;
    }

    //importacap de profissionais
    $id_profissional = $data[7];
    $nome_profissional = $data[8];
    $profissional_db_result = mysqli_query($connMedical,"SELECT * FROM profissionais 
                                                            WHERE nome = '$nome_profissional'",MYSQLI_USE_RESULT);

    $profissional_db = mysqli_fetch_assoc($profissional_db_result);
    mysqli_free_result($profissional_db_result);

    if ($profissional_db) {
      //mapeia profissional existente no banco
      $profissionais[$id_profissional] = $profissional_db['id'];
    } else {
      //cria novo profissional e mapeia o id retornado
      mysqli_query($connMedical, "INSERT INTO profissionais (nome) 
                                      VALUES ('$nome_profissional')");
      $novo_profissional_id = mysqli_insert_id($connMedical);
      $profissionais[$id_profissional] = $novo_profissional_id;
    }


    //importacap de procedimentos
    $nome_procedimento = $data[11];
    $procedimento_db_result = mysqli_query(
      $connMedical,
      "SELECT * FROM procedimentos 
                                             WHERE nome = '$nome_procedimento'",
      MYSQLI_USE_RESULT
    );

    $procedimento_db = mysqli_fetch_assoc($procedimento_db_result);
    mysqli_free_result($procedimento_db_result);

    if ($procedimento_db) {
      //mapeia convenio existente no banco
      $procedimentos[$nome_procedimento] = $procedimento_db['id'];
    } else {
      //cria novo convenio e mapeia o id retornado
      mysqli_query($connMedical, "INSERT INTO procedimentos (nome, descricao) 
                                      VALUES ('$nome_procedimento', 'Procedimento Cadastrado Importacao')");
      $novo_procedimento_id = mysqli_insert_id($connMedical);
      $procedimentos[$nome_procedimento] = $novo_procedimento_id;
    }
  }
  fclose($handle);
}

if (($handle = fopen("./dados/20210512_pacientes.csv", "r")) !== FALSE) {
  fgets($handle);
  while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    //importacao de convenios
    $id_paciente = $data[0];
    $nome_paciente = $data[1];
    $nasc_paciente = date_format(DateTime::createFromFormat('d/m/Y', $data[2]), 'Y-m-d');
    $cpf_paciente = $data[5];
    $rg_paciente = $data[6];
    $sexo_pac = $data[7] == 'M' ? 'Masculino' : 'Feminino';
    $id_convenio = $data[8];

    $paciente_db_result = mysqli_query($connMedical,"SELECT * FROM pacientes 
                                                        WHERE nome = '$nome_paciente'", MYSQLI_USE_RESULT);

    $paciente_db = mysqli_fetch_assoc($paciente_db_result);
    mysqli_free_result($paciente_db_result);

    if ($paciente_db) {
      //mapeia paciente existente no banco
      $pacientes[$id_paciente] = $paciente_db['id'];
    } else {
      //cria novo paciente e mapeia o id retornado
      mysqli_query($connMedical, "INSERT INTO pacientes (nome, sexo, nascimento, cpf, rg, id_convenio) 
                                    VALUES ('$nome_paciente','$sexo_pac','$nasc_paciente','$cpf_paciente','$rg_paciente','$convenios[$id_convenio]')");
      $novo_paciente_id = mysqli_insert_id($connMedical);
      $pacientes[$id_paciente] = $novo_paciente_id;
    }
    //var_dump($pacientes);
  }
  fclose($handle);
}

if (($handle = fopen("./dados/20210512_agendamentos.csv", "r")) !== FALSE) {
  fgets($handle);
  while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    //importacao de convenios
    $hora_inicio = date_format(DateTime::createFromFormat('d/m/Y', $data[2]), 'Y-m-d') . ' ' . $data[3];
    $hora_fim = date_format(DateTime::createFromFormat('d/m/Y', $data[2]), 'Y-m-d') . ' ' . $data[4];
    $cod_paciente = $data[5];
    $cod_profissional = $data[7];
    $cod_convenio = $data[9];
    $nome_procedimento = $data[11];
    $observacoes = $data[1];

    mysqli_query($connMedical, "INSERT INTO agendamentos (id_paciente, id_profissional, dh_inicio, dh_fim, id_convenio, id_procedimento,observacoes) 
                                   VALUES ('$pacientes[$cod_paciente]','$profissionais[$cod_profissional]','$hora_inicio','$hora_fim','$convenios[$cod_convenio]',
                                           '$procedimentos[$nome_procedimento]','$observacoes')");

    //var_dump($pacientes);
  }
  fclose($handle);
}

$dir = './dump.sql';
$dump = exec("mysqldump --user={$user} --password={$pass} --host={$host} {$banco} --result-file={$dir}", $output);
//echo $dump;

//Encerrando as conexões:
$connMedical->close();
$connTemp->close();

// Informações de Fim da Migração:
echo "Fim da Migração: " . dateNow() . ".\n";
