#!/bin/bash
echo "export PATH=/usr/local/bin:$PATH" >> ~/.bashrc
echo "alias sudo=\"sudo env PATH=$PATH\"" >> ~/.bashrc