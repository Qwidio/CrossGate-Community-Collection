<div align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="img/cgcc_logos_widetmp.png">
    <img src="https://github.com/Qwidio/CrossGate-Community-Collection/blob/main/img/cgcc_logos_widetmp.png" width="50%" alt="logo">
  </picture>
  <h1>CGCC</h1>
  <h3>CrossGate Community Collection</h3>
</div>
<br>

## About this project?
  Originally created to announce update about upcoming project, CGCC now becomes Software & Game distribution platform with community forum to share interaction and feedback, the main component of the CrossGate desktop app API & utility

## Documentation & Journals
  for documentation on how to use, please refer to [website documentation](https://porosive.com/documentation/docs.php)<br>
  changelog and in progress improvement can be seen on [this page](https://porosive.com/documentation/changelog.php)<br>
  I'd write about all the past progression [on this journals](https://github.com/Qwidio/CrossGate-Community-Collection/blob/main/journals.md)


## Using the client
### Via release binaries (recommended)
  1. **Prerequisite**: Using windows 10 or newer (Linux/macOS release unavailable)
  2. Download release for from the [release page](https://github.com/Qwidio/CrossGate-Community-Collection/releases/)
  3. extract the files
  4. Run the binary:
   * **Windows**:
     ```powershell
     .\cgccl-stable-amd64.exe
     ```
  * note: do not extract the release files into program folder in windows or any other folder that required admin level access as it can cause to break the program even given administrator access


### Building from Source
  1. **Prerequisite**: Python 3.12(64-bit)
  2. Clone, install dependencies:
   ```bash
   git clone https://github.com/Qwidio/CrossGate-Community-Collection.git
   cd CrossGate-Community-Collection/barenative/
   pip install -r requirements.txt
   ```
  3. test run:
   * **experimental version**:
   ```bash
   cd experimental
   python startup.py
   ```
   * **stable version**:
   ```bash
   cd stable
   python main.py
   ```
  4. Build:
  * **You can edit the `main.spec` configuration if needed and build**:
   ```bash
  pyinstaller main.spec
   ```
  * **Or alternatively fresh build and later edit the `main.spec`**:
   ```bash
  pyinstaller --noconsole --onefile --name "CGCCL" main.py
   ```
  5. run the result:
   ```bash
   ./dist/main.exe
   ```


## Running Web-Server locally
### Prerequisites
  On your target machine it is recommended to have 
  XAMPP(version: 8.2.12, Control Panel version: 3.3.0) installed in your enviroment,
  <br>
  or the similiar server stack like below as alternative:
    - Apache/2.4.58 (Win64)
    - Database client version: libmysql - mysqlnd 8.2.12
    - PHP 8.2.12

### Setup (Assumed using XAMPP)
  1. Download the [project in zip file](https://github.com/Qwidio/CrossGate-Community-Collection/archive/refs/heads/main.zip) and extract
   <br>
   OR
   <br>
   Clone repository
   ```bash
   git clone https://github.com/Qwidio/CrossGate-Community-Collection.git
   ```
  2. move the downloaded/extracted repository to the public directory of you apache server (`htdocs/` in case of XAMPP)
  3. Import the `cgcc.sql` from the `sql` directory to your MySQL database and make sure the database name are the same in your database config file on `processes/database.php`.
  4. set your apache server to use the same configuration as `.htaccess` files in this repository


## Credits
  [Cure53](https://github.com/cure53), [DOMPurify](https://github.com/cure53/DOMPurify) used for MarkDown XSS Sanitizer<br>
  [MarketingPipeline](https://github.com/MarketingPipeline), [MarkDown Tag](https://github.com/MarketingPipeline/Markdown-Tag) for MarkDown Renderer


## License
  Distributed under the MIT License. See [LICENSE](./LICENSE) for details.