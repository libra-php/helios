// Auth keypad
document.querySelectorAll('.key').forEach(button => {
    button.addEventListener('click', () => {
        const digit = button.value; 
        const input = document.getElementById('code'); // Select the input field

        if (digit.toLowerCase() === 'bs') {
            // Remove the last character from the input value
            input.value = input.value.slice(0, -1);
        } else {
            if (input.value.length < 6) {
                // Append the digit to the input's current value
                input.value += digit;
            }
        }
    });
});
