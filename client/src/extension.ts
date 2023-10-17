// The module 'vscode' contains the VS Code extensibility API
// Import the module and reference it with the alias vscode in your code below
import * as vscode from 'vscode';
import { LanguageClient, ServerOptions, LanguageClientOptions, StreamInfo } from 'vscode-languageclient/node';
import * as net from 'net';
let languageClient: LanguageClient;

export function activate(context: vscode.ExtensionContext) {
	const serverOptions = () => {
		let socket = net.connect({
			host: '127.0.0.1',
			port: 3000,
		});

		let result = <StreamInfo>{
			writer: socket,
			reader: socket
		};

		return Promise.resolve(result);
	};

	const clientOptions: LanguageClientOptions = {
		// Register the server for plain text documents
		documentSelector: [{ scheme: 'file', language: 'plaintext' }],
		synchronize: {
			// Notify the server about file changes to '.clientrc files contained in the workspace
			fileEvents: vscode.workspace.createFileSystemWatcher('**/.txt')
		},
	};

	languageClient = new LanguageClient(
		'client',
		'bla bla',
		serverOptions,
		clientOptions
	);

	languageClient.start();
}

// This method is called when your extension is deactivated
export function deactivate() { }
