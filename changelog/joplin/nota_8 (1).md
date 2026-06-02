---
### Alpha Engine: Criação e Relacionamento de ZoneDescription
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação da entidade `ZoneDescription.php` tipada para o PHP 8.4, com os devidos mapeamentos `#[ManyToOne]` para `Zone` e `Language`.
- Orientação estrutural para injetar a coleção `descriptions` utilizando `#[OneToMany]` na entidade legada `Zone.php`.
**Benefícios:** Expansão da capacidade de localização e tradução do sistema. Modelar as zonas (Estados/Departamentos) com entidades ricas de tradução garante precisão máxima de idioma na emissão de notas fiscais e relatórios logísticos.
