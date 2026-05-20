/* Verificar se as senhas digitadas são iguais */
/*
const input1 = document.getElementById('User_password');
const input2 = document.getElementById('repeat-password');
function verificarCampos() {
  if (input1.value === input2.value) {
    input2.setCustomValidity('');
  } else {
    input2.setCustomValidity('As senhas devem ser iguais.');
  } 
}
// Adiciona listeners para o evento 'input' em ambos os campos
// O evento 'input' é acionado sempre que o valor do input muda.
input1.addEventListener('input', verificarCampos);
input2.addEventListener('input', verificarCampos);


*/
// Seleciona o campo de input
const inputTelefone = document.getElementsByName('telephone');
if (inputTelefone.length > 0) {
    inputTelefone[0].addEventListener('input', function (event) {
        let valor = event.target.value;
        // 1. Limpa o valor, removendo tudo que não for dígito
        valor = valor.replace(/\D/g, '');

        // 2. Aplica a máscara de acordo com o tamanho do valor
        if (valor.length > 10) {
            // Formato com 9 dígitos: (99) 99999-9999
            valor = valor.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
        } else if (valor.length > 5) {
            // Formato com 8 dígitos: (99) 9999-9999
            valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (valor.length > 2) {
            // Adiciona apenas o parêntese do DDD
            valor = valor.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        } else {
            // Remove tudo se o usuário apagar o primeiro parêntese
            valor = valor.replace(/^(\d*)/, '($1');
        }
        // 3. Atualiza o valor do campo de input
        event.target.value = valor;
    });
}


// Seleciona o campo de input
const inputCpf = document.getElementsByName('cpf_cnpj');
if (inputCpf.length > 0) {                         

    inputCpf[0].addEventListener('input', function (eventCPF) {
        let valorCPF = eventCPF.target.value;
        
        // 1. Limpa o valorCPF, removendo tudo que não for dígito
        valorCPF = valorCPF.replace(/\D/g, '');
        // 2. Verifica CPF antes de formatar
        let valid = false;
        let soma;
        if (valorCPF.length == 11) {  
            if (/^(?!^(\d)\1+$)\d{11}$/.test(valorCPF)) { // Testa se são números repetidos      
                for (i = 9; i < 11; i++) {
                    for (soma = 0, j = 0; j < i; j++) {
                        soma += parseInt(valorCPF.charAt(j)) * ((i + 1) - j);
                    }
                    soma = ((10 * soma) % 11) % 10;
                    if (parseInt(valorCPF.charAt(j)) == soma) {   
                        valid = true;
                    }
                }
            } 
        }  

        if(valid){
            // inputCpf[0].classList.add('valid');
            document.getElementById('cpf_cnp-status').textContent = '✔️'; // Caractere de "checado"
            document.getElementById('cpf_cnp-status').style.color = 'green';
        }else{
            // inputCpf[0].classList.remove('valid');
            document.getElementById('cpf_cnp-status').textContent = ''; // Caractere de "checado"
        }
        // 3. Aplica a máscara de acordo com o tamanho do valorCPF
        if (valorCPF.length > 9) {
        valorCPF = valorCPF.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2}).*/, '$1.$2.$3-$4'); // Formato com 11 dígitos: 999.999.999-99
        } else if (valorCPF.length > 6) {
        
        valorCPF = valorCPF.replace(/^(\d{3})(\d{3})(\d)/, '$1.$2.$3'); // Formato com 9 dígitos: 999.999.999
        } else if (valorCPF.length > 3) {
        valorCPF = valorCPF.replace(/^(\d{3})(\d)/, '$1.$2'); // Formato com 6 dígitos: 999.999
        }
        eventCPF.target.value = valorCPF; // 3. Atualiza o valorCPF do campo de input
    });
}
/*


document.addEventListener('DOMContentLoaded', () => {
    // Pega o formulário, o botão de envio e todos os inputs exceto o submit
    const form = document.getElementById('signupForm');
    const btnSubmit = document.getElementById('signup-botao');
    const inputs = form.querySelectorAll('input:not([type="submit"])');
  
    // Adiciona um listener para o evento 'input' em cada campo
    inputs.forEach(input => {
      input.addEventListener('input', () => {
        // Verifica se todos os inputs são válidos
        const todosValidos = Array.from(inputs).every(input => input.validity.valid);
        
        // Habilita ou desabilita o botão de envio
        btnSubmit.disabled = !todosValidos;
      });
    });
  });

  */
