(function () {
    const patrones = {
        nombres: /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]{2,60}$/,
        apPat: /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]{2,60}$/,
        apMat: /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]{2,60}$/,
        tel: /^(\+52[\s-]?)?[0-9]{10}$/,
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        marca: /^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s'-]{1,40}$/,
        modelo: /^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s'-]{1,40}$/,
        año: /^(19[8-9][0-9]|20[0-2][0-9]|203[0-5])$/,
        placa: /^[A-Z0-9]{3,4}[-\s]?[0-9]{2,3}[-\s]?[A-Z0-9]{0,3}$/i,
        VIN: /^[A-HJ-NPR-Z0-9]{17}$/i
    };

    const mensajes = {
        nombres: 'Nombres: solo letras, de 2 a 60 caracteres.',
        apPat: 'Apellido paterno: solo letras, de 2 a 60 caracteres.',
        apMat: 'Apellido materno: solo letras, de 2 a 60 caracteres.',
        tel: 'Teléfono: 10 dígitos (opcional +52 al inicio).',
        email: 'Email: formato no válido.',
        marca: 'Marca: letras y números, hasta 40 caracteres.',
        modelo: 'Modelo: letras y números, hasta 40 caracteres.',
        año: 'Año: entre 1980 y 2035.',
        placa: 'Placa: formato no válido (ej. ABC1234).',
        VIN: 'VIN: exactamente 17 caracteres alfanuméricos.'
    };

    const form = document.getElementById('formClientes');
    if (!form) {
        return;
    }

    function mostrarError(input, texto) {
        const grupo = input.closest('.input-group');
        if (!grupo) {
            return;
        }
        let msg = grupo.querySelector('.error-msg');
        if (!msg) {
            msg = document.createElement('span');
            msg.className = 'error-msg';
            grupo.appendChild(msg);
        }
        msg.textContent = texto;
        input.classList.add('input-error');
    }

    function limpiarError(input) {
        const grupo = input.closest('.input-group');
        if (!grupo) {
            return;
        }
        const msg = grupo.querySelector('.error-msg');
        if (msg) {
            msg.textContent = '';
        }
        input.classList.remove('input-error');
    }

    function validarCampo(input) {
        const id = input.id || input.name;
        const patron = patrones[id];
        if (!patron) {
            return true;
        }

        let valor = input.value.trim();
        
        // Si estamos editando y todos los campos de vehículo están vacíos, omitir validación de vehículo
        const isEdit = document.getElementById('clave-cliente-hidden') && document.getElementById('clave-cliente-hidden').value !== '';
        const vehCampos = ['marca', 'modelo', 'año', 'placa', 'VIN'];
        if (isEdit && vehCampos.includes(id)) {
            const todosVacios = vehCampos.every(cId => {
                const inp = document.getElementById(cId);
                return !inp || inp.value.trim() === '';
            });
            if (todosVacios) {
                limpiarError(input);
                return true;
            }
        }

        if (id === 'tel') {
            valor = valor.replace(/\s/g, '');
        }
        if (id === 'placa' || id === 'VIN') {
            valor = valor.toUpperCase();
            input.value = valor;
        }

        if (!patron.test(valor)) {
            mostrarError(input, mensajes[id]);
            return false;
        }

        limpiarError(input);
        return true;
    }

    Object.keys(patrones).forEach(function (id) {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('blur', function () {
                validarCampo(input);
            });
        }
    });

    form.addEventListener('submit', function (e) {
        let valido = true;
        Object.keys(patrones).forEach(function (id) {
            const input = document.getElementById(id);
            if (input && !validarCampo(input)) {
                valido = false;
            }
        });

        if (!valido) {
            e.preventDefault();
            alert('Revise los campos marcados en rojo. Los datos no cumplen el formato requerido.');
        }
    });
})();
