
var display = document.getElementById('screen');
var buttons = document.getElementsByClassName('button');

//Declare which buttons to display and buttons with functions (i feel like there's a beeter way to do this, maybe Jquery?)
Array.prototype.forEach.call(buttons, function(button) {
button.addEventListener('click', function() {
    if (button.textContent != '=' &&
        button.textContent != 'C' &&
        button.textContent != 'x' &&
        button.textContent != '÷' &&
        button.textContent != '√' &&
        button.textContent != 'x ²' &&
        button.textContent != '%' &&
        button.textContent != '<=' &&
        button.textContent != '±' &&
        button.textContent != 'sin' &&
        button.textContent != 'cos' &&
        button.textContent != 'tan' &&
        button.textContent != 'log' &&
        button.textContent != 'ln' &&
        button.textContent != 'x^' &&
        button.textContent != 'x !' &&
        button.textContent != 'π' &&
        button.textContent != 'e' &&
        button.textContent != 'rad' &&
        button.textContent != '∘') {
        display.value += button.textContent;
    } else if (button.textContent === '=') {
        equals();
    } else if (button.textContent === 'C') {
        clear();
    } else if (button.textContent === 'x') {
        multiply();
    } else if (button.textContent === '÷') {
        divide();
    } else if (button.textContent === '±') {
        plusMinus();
    } else if (button.textContent === '<=') {
        backspace();
    } else if (button.textContent === '%') {
        percent();
    } else if (button.textContent === 'π') {
        pi();
    } else if (button.textContent === 'x ²') {
        square();
    } else if (button.textContent === '√') {
        squareRoot();
    } else if (button.textContent === 'sin') {
        sin();
    } else if (button.textContent === 'cos') {
        cos();
    } else if (button.textContent === 'tan') {
        tan();
    } else if (button.textContent === 'log') {
        log();
    } else if (button.textContent === 'ln') {
        ln();
    } else if (button.textContent === 'x^') {
        exponent();
    } else if (button.textContent === 'x !') {
        factorial();
    } else if (button.textContent === 'e') {
        exp();
    } else if (button.textContent === 'red') {
        radians();
    } else if (button.textContent === '∘') {
        degrees();
    }
});
});


//Define syntaxError function.

function syntaxError() {
if (eval(display.value) == SyntaxError || eval(display.value) == ReferenceError || eval(display.value)== TypeError) {
    display.value == 'Syntax Error';
}
}

//Define op functions for buttons with functions

function equals() {
if ((display.value).indexOf("^") > -1) {
var base = (display.value).slice(0, (display.value).indexOf("^"));
var exponent = (display.value).slice((display.value).indexOf("^") + 1);
display.value = eval("Math.pow(" + base + "," + exponent +")");
} else {
display.value = eval(display.value)
syntaxError()
}
}

//Define clear and backspace functions

function clear() {
display.value = '';
}

function backspace() {
display.value = display.value.substring(0, display.value.length - 1);
}

//Define simple op functions

function multiply() {
display.value += '*';
}

function divide() {
display.value += '/';
}

function plusMinus() {
if (display.value.charAt(0) === '-') {
    display.value = display.value.slice(1);
} else {
    display.value = '-' + display.value;
}
}

//Define complex op functions

function factorial() {
if (display.value === 0) {
    display.value = '1';
} else if (display.value < 0) {
    display.value = 'undefined';
} else {
    var number = 1;
    for (var i = display.value; i > 0; i--) {
        number *= i;
    }
    display.value = number;
}
}

function pi() {
display.value = (display.value * Math.PI);
}

function square() {
display.value = eval(display.value * display.value);
}

function squareRoot() {
display.value = Math.sqrt(display.value)
}

function percent() {
display.value = display.value / 100;
}

function sin() {
display.value = Math.sin(display.value);
}

function cos() {
display.value = Math.cos(display.value);
}

function tan() {
display.value = Math.tan(display.value);
}

function log() {
display.value = Math.log10(display.value);
}

function ln() {
display.value = Math.log(display.value);
}

function exponent() {
display.value += '^';
}

function exp() {
display.value = Math.exp(display.value);
}

function radians() {
display.value = display.value * (Math.PI / 180)
}

function degrees() {
display.value = display.value * (180 / Math.PI);
}

// SUM WHEN PRESS ENTER 
var wage = document.getElementById("screen");
wage.addEventListener("keydown", function (e) {
// console.log(e.code)
if (e.code === "Enter" || e.code === "NumpadEnter") {  //checks whether the pressed key is "Enter"
    equals();
}
});
