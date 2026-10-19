Builds assets, commits, pushes, and deploys to the Serverbyt server.

Option 1: PowerShell
  cd D:\PROJECTS\agapeinfosys
  .\deploy-local.ps1
  .\deploy-local.ps1 -Message "build: fix header"
  .\deploy-local.ps1 -SkipDeploy          (build + push only, no server deploy)

Option 2: CMD
  deploy-local.bat
  deploy-local.bat -Message "build: fix header"

Steps run, in order:
  1/5  npm run build
  2/5  git add -f public/build
  3/5  git commit -m <message>
  4/5  git push origin main
  5/5  SSH to the server: git fetch + reset --hard origin/main + bash deploy.sh

Deploy step (5/5) notes:
  - Needs an SSH key. It looks for .\deploy_key first, then the
    temp key used by this project. Override with -SshKey <path>.
  - Override the server with -DeployTarget user@host.
  - If no key is found the script only pushes and reminds you to deploy
    manually in Serverbyt (Repository > Deploy), which runs deploy.sh.
  - -SkipDeploy stops after the push, leaving the server untouched.
