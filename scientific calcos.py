
print("scientific calculator")
print("operations:+,-*,/,sqrt,pow,sin,cos,tan,exit")
while True:
    op=input("\nEnter operations").lower()
    if op =="exist":
        print("Exiting calculator.")
        break
    if op in ['+','-','*','/']:
        a = float(input("Enter first number:"))
        b = float(input("Enter second number"))
        if op =='+':
            print("result:", a + b)
        elif op == '-':
            print("result:", a - b)
        elif op =='*':
            print("result:", a * b)
        elif op == '/':
            if b == 0:
                print("Error: Divusion by zero.")
            else:
                print("result:", a / b)
    elif op == 'sqrt':
        a = float(input("Enter number:"))
        if a < 0:
            print("Error: cannot take square root of negative number.")
    elif op == 'pow':
        a = float(input("Enter base:"))
        b = float(input("Enter exponent:"))
        print("result: math.pow(a ,b,)")
    elif op in ['sin','cos','tan']:
        angle = float(input("Enter angle in degrees:"))
        radians = math.radians(angle)
        if op == 'sin':
         op == 'cos'
        elif op == 'tan':