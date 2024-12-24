# Uplo Magento 2 Assessment

This repository contains the **ProductImport** module for Magento 2, designed to facilitate importing and validating product and category data from CSV files.

## Overview
In the Magento sidebar, the "Uplo Assessment" section has been added to manage product and category imports.

![Screenshot 2024-12-24 073604](https://github.com/user-attachments/assets/00f6ce83-b7a3-4531-92ac-d2d0a138ed3b)

## Routes and Features

### 1. **Manage CSV**
![Screenshot 2024-12-24 071616](https://github.com/user-attachments/assets/1ae3d969-5a30-40c5-a6cd-8c5df6a4b42e)

This section features a grid view for the records in the database that are awaiting CSV validation after being uploaded. 

- **Product CSV validation** will fail if:
  - The SKU format is incorrect.
  - The URL key is invalid.
  - Required fields like "name", "url key", or "SKU" are missing.

- **Category CSV validation** will fail if:
  - The URL key is invalid or missing.
  - The category name is missing.

After uploading two files and clicking **Save**, two records will be added for separate validation.

![Screenshot 2024-12-24 072121](https://github.com/user-attachments/assets/d06f4dcb-a4f7-4388-82e3-d9d5ffdbe6ac)

### 2. **Categories & Products**
![Screenshot 2024-12-24 072204](https://github.com/user-attachments/assets/8e8eeaf0-2288-492c-9701-3f9af51723fc)

This section displays a grid view of successfully validated product and category entities from the uploaded CSVs. 

- If a product with the same SKU already exists, its data will be updated. If the SKU is new, a new product will be created.
- Similarly, if a category with the same name exists, it will be updated; otherwise, a new category will be created.

### 3. **Cronjob Results**
![Screenshot 2024-12-24 072709](https://github.com/user-attachments/assets/06ffc81a-66a0-403f-a10e-1491012f0a8a)

This section displays the results of the cronjob process, which runs every 5 minutes. The cronjob handles tasks related to importing and updating records in the system.

---

### Key Features:
- **Grid tables**: All grid tables include features like filtration, pagination, and sorting for easy navigation and management.
