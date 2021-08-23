CREATE TABLE accounts (id INT(8), 
email VARCHAR(200), 
balance INT(20),
PRIMARY KEY (id));

CREATE TABLE transfers 
(id_transfer INT(10) NULL AUTO_INCREMENT, 
id_origen INT(8), 
id_destino INT(8), 
monto INT(20), 
FOREIGN KEY (id_origen) REFERENCES accounts(id), 
FOREIGN KEY (id_destino) REFERENCES accounts(id), 
PRIMARY KEY (id_transfer, id_origen, id_destino));

CREATE TABLE deposits 
(id_deposit INT(10) NULL AUTO_INCREMENT, 
id_destino INT(8), 
monto INT(20), 
FOREIGN KEY (id_destino) REFERENCES accounts(id), 
PRIMARY KEY (id_deposit));

CREATE TABLE withdrawals 
(id_withdrawl INT(20) NULL AUTO_INCREMENT, 
id_origen INT(8), 
monto INT(20), 
FOREIGN KEY (id_origen) REFERENCES accounts(id),
PRIMARY KEY (id_withdrawl));