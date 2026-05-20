
document.getElementById('input-postcode').addEventListener('blur', function() {
	const cep = this.value.replace(/\D/g, ''); // Remove caracteres não numéricos [5]

    if (cep.length === 8) {
        // Faz a requisição para a API ViaCEP
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    // Preenche os campos se o CEP existir
                    document.getElementById('input-address-1').value = data.logradouro;
                    document.getElementById('input-neighborhood').value = data.bairro;
                    document.getElementById('input-city').value = data.localidade;
					$("select option:contains('"+data.estado+"')").prop("selected", true);

                } else {
                    alert('CEP não encontrado.');
                }
            })
            .catch(error => console.error('Erro:', error));
    } else {
        alert('CEP inválido.');
    }
});

/* Pontos importantes:

    Evento blur: O preenchimento ocorre quando o usuário sai do campo CEP.
    Limpeza de CEP: replace(/\D/g, '') remove pontos ou traços que o usuário possa digitar.
    API ViaCEP: Usada para retornar os dados em formato JSON.
    Validação: O código verifica se o CEP tem 8 dígitos e se a API retornou um erro (CEP não encontrado). 
	
*/
