document.addEventListener('DOMContentLoaded', () => {
    const resultForm = document.getElementById('result-form');
    const resultDisplay = document.getElementById('result-display');

    if (resultForm) {
        resultForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(resultForm);
            resultDisplay.innerHTML = '<p>Loading...</p>'; // Show loading message

            try {
                const response = await fetch('php/fetch_result.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    displayResult(data);
                } else {
                    resultDisplay.innerHTML = <p class="error-message">${data.message}</p>;
                }
            } catch (error) {
                resultDisplay.innerHTML = <p class="error-message">An error occurred. Please try again.</p>;
                console.error('Error:', error);
            }
        });
    }

    function displayResult(data) {
        const { student_info, results } = data;

        // Student Details
        let html = `
            <div class="student-info">
                <p><strong>Student Name:</strong> ${student_info.student_name}</p>
                <p><strong>Register Number:</strong> ${student_info.register_number}</p>
            </div>
        `;
        
        // Results Table
        html += `
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>CIA 1 (25)</th>
                        <th>CIA 2 (25)</th>
                        <th>Internal (25)</th>
                        <th>Semester (75)</th>
                        <th>Total (100)</th>
                    </tr>
                </thead>
                <tbody>
        `;

        results.forEach(res => {
            html += `
                <tr>
                    <td>${res.subject_code}</td>
                    <td>${res.subject_name}</td>
                    <td>${res.cia1}</td>
                    <td>${res.cia2}</td>
                    <td>${res.internal}</td>
                    <td>${res.semester_exam}</td>
                    <td><strong>${res.total}</strong></td>
                </tr>
            `;
        });

        html += '</tbody></table>';

        resultDisplay.innerHTML = html;
    }
});