<?php

return [
	'test_data' => [
		'shouldKeepSpaceBeforeAttributeInNestedLiteral' => [
			'input'    => 'const f=(e)=>html`<div>${e.list.map(x=>html`<rndzz-mentions .designConfiguration="${x}"></rndzz-mentions>`)}</div>`;',
			'expected' => 'const f=(e)=>html`<div>${e.list.map(x=>html`<rndzz-mentions .designConfiguration="${x}"></rndzz-mentions>`)}</div>`',
		],
		'shouldKeepTextSpacesInNestedLiteral' => [
			'input'    => 'const f=(e)=>html`<ul>${e.items.map(i=>html`<li class="a"> ${i} </li>`)}</ul>`;',
			'expected' => 'const f=(e)=>html`<ul>${e.items.map(i=>html`<li class="a"> ${i} </li>`)}</ul>`',
		],
		'shouldKeepDeeplyNestedLiterals' => [
			'input'    => 'const f=(e)=>html`<a>${e.x?html`<b>${e.y?html`<c> 1 </c>`:""} 2 </b>`:""} 3 </a>`;',
			'expected' => 'const f=(e)=>html`<a>${e.x?html`<b>${e.y?html`<c> 1 </c>`:""} 2 </b>`:""} 3 </a>`',
		],
		'shouldKeepSimpleLiteral' => [
			'input'    => 'const a = html`<p class="x">  hi  </p>` ;',
			'expected' => 'const a=html`<p class="x">  hi  </p>`',
		],
		'shouldKeepEmptyLiteral' => [
			'input'    => 'var  e = `` ;',
			'expected' => 'var e=``',
		],
		'shouldKeepDollarNotStartingSubstitution' => [
			'input'    => 'var  d = `cost: $5  ${ x }  $ {y}` ;',
			'expected' => 'var d=`cost: $5  ${ x }  $ {y}`',
		],
		'shouldKeepMultilineLiteral' => [
			'input'    => "var  m = `line 1\n    line 2  ` ;",
			'expected' => "var m=`line 1\n    line 2  `",
		],
		'shouldKeepSiblingLiterals' => [
			'input'    => 'var  s = [ `a  b`, `c  ${d}  e` ] ;',
			'expected' => 'var s=[`a  b`,`c  ${d}  e`]',
		],
		'shouldKeepObjectLiteralInSubstitution' => [
			'input'    => 'const s=css`a{b:${({c:1}).c}px} d{e:f}`;  var  q = 1 ;',
			'expected' => 'const s=css`a{b:${({c:1}).c}px} d{e:f}`;var q=1',
		],
		'shouldKeepBraceInStringInSubstitution' => [
			'input'    => 'const t=`x ${ "}" } y`; var  z = 2;',
			'expected' => 'const t=`x ${ "}" } y`;var z=2',
		],
		'shouldKeepEscapedBacktick' => [
			'input'    => 'const u=`a\` b ${1} c`; var  w = 3;',
			'expected' => 'const u=`a\` b ${1} c`;var w=3',
		],
		'shouldKeepNestedLiteralAfterRegexWithQuotes' => [
			'input'    => 'var q = html`<a>${ s.replace(/"/g, "\'") }</a> ${ x.map(i => html`<b> ${i} </b>`) }` ;',
			'expected' => 'var q=html`<a>${ s.replace(/"/g, "\'") }</a> ${ x.map(i => html`<b> ${i} </b>`) }`',
		],
		'shouldKeepNestedLiteralAfterRegexWithBrace' => [
			'input'    => 'var v = html`a ${ s.replace(/}/g, "") } ${ x.map(i => html`<b> ${i} </b>`) }` ;',
			'expected' => 'var v=html`a ${ s.replace(/}/g, "") } ${ x.map(i => html`<b> ${i} </b>`) }`',
		],
		'shouldKeepNestedLiteralAfterDivision' => [
			'input'    => 'var d = html`<a>${ a / b }</a>${ x.map(i => html`<b> ${i} </b>`) }` ;',
			'expected' => 'var d=html`<a>${ a / b }</a>${ x.map(i => html`<b> ${i} </b>`) }`',
		],
		'shouldKeepNestedLiteralAfterLineCommentWithBacktick' => [
			'input'    => "var c = html`<a>\${ // has `tick` and isn't\n  x.map(i => html`<b> \${i} </b>`) }</a>` ;",
			'expected' => "var c=html`<a>\${ // has `tick` and isn't\n  x.map(i => html`<b> \${i} </b>`) }</a>`",
		],
		'shouldKeepNestedLiteralAfterBlockComment' => [
			'input'    => 'var c = html`${ /* {`\'" */ x.map(i => html`<b> ${i} </b>`) }` ;',
			'expected' => 'var c=html`${ /* {`\'" */ x.map(i => html`<b> ${i} </b>`) }`',
		],
		'shouldKeepNestedLiteralAfterPostIncrementDivision' => [
			'input'    => 'var p = html`<a>${ i++ / 2 }</a> ${ x.map(i => html`<b> ${i} </b>`) }` ; var  q = a / b ;',
			'expected' => 'var p=html`<a>${ i++ / 2 }</a> ${ x.map(i => html`<b> ${i} </b>`) }`;var q=a/b',
		],
		'shouldKeepNestedLiteralAfterBacktickInTopLevelRegex' => [
			'input'    => 'var  r = s.split(/`/) ; var  t = html`<a> ${ x.map(i => html`<b> ${i} </b>`) }</a>` ;',
			'expected' => 'var r=s.split(/`/);var t=html`<a> ${ x.map(i => html`<b> ${i} </b>`) }</a>`',
		],
		'shouldKeepNestedLiteralAfterBacktickInTopLevelComment' => [
			'input'    => "// it's `x`\nvar  t = html`<a> \${ x.map(i => html`<b> \${i} </b>`) }</a>` ;",
			'expected' => "var t=html`<a> \${ x.map(i => html`<b> \${i} </b>`) }</a>`",
		],
		'shouldKeepEscapedSubstitution' => [
			'input'    => 'var  e = `a \${b}  c` ;',
			'expected' => 'var e=`a \${b}  c`',
		],
		'shouldKeepTaggedTemplate' => [
			'input'    => 'var  t = tag`a  ${ b }  c` ;',
			'expected' => 'var t=tag`a  ${ b }  c`',
		],
		'shouldKeepLiteralWithRegexAfterReturn' => [
			'input'    => 'var f = `a ${ x.map(y => { return /`/.test(y) }) } b` ; var  k = 4 ;',
			'expected' => 'var f=`a ${ x.map(y => { return /`/.test(y) }) } b`;var k=4',
		],
		'shouldKeepLiteralWithQuoteRegexAfterReturn' => [
			'input'    => 'var f = `a ${ x.map(y => { return /\'/.test(y) }) } b` ; var  k = 4 ;',
			'expected' => 'var f=`a ${ x.map(y => { return /\'/.test(y) }) } b`;var k=4',
		],
		'shouldKeepNestedLiteralAfterPropertyDivision' => [
			'input'    => 'var p = html`<a>${ x.in / 2 }</a> ${ x.map(i => html`<b> ${i} </b>`) }` ;',
			'expected' => 'var p=html`<a>${ x.in / 2 }</a> ${ x.map(i => html`<b> ${i} </b>`) }`',
		],
		'shouldKeepLiteralsAfterTypeofRegexWithBacktick' => [
			'input'    => 'var t88 = css`https://e.com/p ${ c ? `<x-el .p="1"> t </x-el>it\'s <x-el .p="1"> t </x-el><b> x </b>` : \'\' } a  b `;
var t50 = css`${ c ? html` a  b ${ c ? html` a  b ${ typeof /`/ }` : \'\' }it\'s  a  b ` : \'\' }it\'s `;
var u = \'https://x.org/y\';',
			'expected' => 'var t88=css`https://e.com/p ${ c ? `<x-el .p="1"> t </x-el>it\'s <x-el .p="1"> t </x-el><b> x </b>` : \'\' } a  b `;var t50=css`${ c ? html` a  b ${ c ? html` a  b ${ typeof /`/ }` : \'\' }it\'s  a  b ` : \'\' }it\'s `;var u=\'https://x.org/y\'',
		],
		// Expected is develop's output: when a literal can't be parsed, the whole file is minified as before.
		'shouldMatchPreviousBehaviourWhenALiteralFallsBack' => [
			'input'    => 'var t88 = css`https://e.com/p ${ c ? `<x-el .p="1"> t </x-el>it\'s <x-el .p="1"> t </x-el><b> x </b>` : \'\' } a  b `;
var t50 = css`${ c ? html` a  b ${ c ? html` a  b ${ (
      /`/) }` : \'\' }it\'s  a  b ` : \'\' }it\'s `;
var u = \'https://x.org/y\';',
			'expected' => 'var t88=css`https://e.com/p ${ c ? `<x-el.p="1">t</x-el>it\'s <x-el .p="1"> t </x-el><b> x </b>` : \'\' } a  b `;
var t50 = css`${ c ? html` a  b ${ c ? html` a  b ${ (
      /`/) }` : \'\' }it\'s a b ` : \'\' }it\'s `;var u=\'https://x.org/y\'',
		],
		'shouldKeepPreviousBehaviourForUnterminatedLiteral' => [
			'input'    => 'var  u = `a  b ; var  k = 1 ;',
			'expected' => 'var u=`a b;var k=1',
		],
		'shouldMinifyCodeAroundLiterals' => [
			'input'    => 'var  t = `a  ${b}`;  if ( t ) { go ( `c  d` ) ; }',
			'expected' => 'var t=`a  ${b}`;if(t){go(`c  d`)}',
		],
		'shouldMinifyCodeWithoutLiterals' => [
			'input'    => 'var a = "x"  +  \'y\'; if ( a ) { b ( ) ; }',
			'expected' => 'var a="x"+\'y\';if(a){b()}',
		],
	],
];
