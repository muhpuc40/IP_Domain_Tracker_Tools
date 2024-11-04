document.getElementById("domainForm").addEventListener("submit", async function(event) {
    event.preventDefault();
    const domain = document.getElementById("domainInput").value;
    const resultDiv = document.getElementById("result");
    resultDiv.innerHTML = "Checking domain health...";

    try {
        const response = await fetch(`../api/check_domain.php?domain=${encodeURIComponent(domain)}`);
        const data = await response.json();

        if (data.error) {
            resultDiv.innerHTML = `<p style="color:red">${data.error}</p>`;
        } else {
            resultDiv.innerHTML = `
                <h2>Domain Health Report for ${domain}</h2>
                <p><strong>DNS:</strong> ${JSON.stringify(data.DNS)}</p>
                <p><strong>SPF:</strong> ${data.SPF}</p>
                <p><strong>DMARC:</strong> ${data.DMARC}</p>
                <p><strong>Blacklist:</strong></p>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Host</th>
                            <th>List</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.isArray(data.Blacklist) ? data.Blacklist.map(item => `
                            <tr>
                                <td>${item.category}</td>
                                <td>${item.host}</td>
                                <td>${item.list}</td>
                            </tr>
                        `).join('') : `<tr><td colspan="3">${data.Blacklist}</td></tr>`}
                    </tbody>
                </table>
                <p><strong>SSL Expiry Date:</strong> ${data['SSL Expiry Date']}</p>
                <p><strong>Performance:</strong> ${data.Performance}</p>
                <p><strong>MX Records:</strong> ${JSON.stringify(data['MX Records'])}</p>
                <p><strong>MX Blacklist:</strong> ${JSON.stringify(data['MX Blacklist'])}</p>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `<p style="color:red">Error: Unable to fetch domain health data</p>`;
        console.error(error);
    }
});
